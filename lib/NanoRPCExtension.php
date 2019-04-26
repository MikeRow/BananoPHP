<?php

	/*

	USAGE:

	Include NanoRPC.php
		
		require_once('PATH/php4nano/lib/NanoRPC.php');
		
	Include NanoRPCExtension.php
		
		require_once('PATH/php4nano/lib/NanoRPCExtension.php');
	
	Initialize Nano connection/object
	
		$nanorpc = new NanoRPCExtension();
		
	Example of call:

		$args =
		[
			'string' => 'hello'
		];
		
		$response = $nanorpc->vanity_account( $args );
		
		print_r( $response );
		
	This is an extension class, native RPC are still available:
	
		$args =
		[
			'account' => 'nano_1abcp8j755owefwsxcbww56jqmimsojy1xxduz7m3idze677hkrnjs98da55'
		];
		
		$response = $nanorpc->account_balance( $args );
		
		echo $response['balance'];
	
	*/
	
	
	
	class NanoRPCExtension extends NanoRPC
	{
	
	
	
		// *** Send all funds from ['wallet'] to ['destination'] ***
		
		
		
		public function wallet_wipe( array $args )
		{
			
			// Check all args
			
			if( !isset( $args['wallet'] ) || !isset( $args['destination'] ) )
			{
				return ['error'=>'Unable to parse Array'];
			}
			
			$wallet = $args['wallet'];
			
			$destination = $args['destination'];
			
			// Wallet ok?
			
			$wallet_info = $this->wallet_info( ['wallet' => $wallet] );
			
			if( isset( $wallet_info['error'] ) )
			{
				return ['error'=>'Bad wallet number'];
			}
			
			// Balance ok?
			
			if( gmp_cmp( $wallet_info['balance'], 1 ) < 0 )
			{
				return ['error'=>'Insufficient balance'];
			}
			
			// Destination ok?
			
			$check_destination = $this->validate_account_number( ['account'=>$destination] );
			
			if( $check_destination['valid'] != 1 )
			{
				return ['error'=>'Bad destination'];
			}
			
			
			
			// Execution
		
		
		
			$return = ['balances' => []];
			
			// Get wallet balances
			
			$args =
			[
				'wallet' => $wallet,
				'threshold' => 1
			];
			
			$wallet_balances = $this->wallet_balances( $args );
			
			// Sort from higher to lower balance
			
			uasort( $wallet_balances['balances'], function( $a, $b )
			{
				
				return gmp_cmp( $b['balance'], $a['balance'] );
				
			});
			
			// Wipe wallet
			
			foreach( $wallet_balances['balances'] as $account => $balances )
			{
				
				$args =
				[
					'wallet' => $wallet,
					'source' => $account,
					'destination' => $destination,
					'amount' => $balances['balance'],
					'id' => uniqid()
				];
				
				$send = $this->send($args);
				
				if( $send['block'] != '0000000000000000000000000000000000000000000000000000000000000000' )
				{
				
					$return['balances'][$account] =
					[
						'block' => $send['block'],
						'amount' => $balances['balance']
					];
				
				}
				else
				{
				
					$return['error'] = 'Insufficient balance';
				
					$return['balances'][$account] =
					[
						'block' => '0000000000000000000000000000000000000000000000000000000000000000',
						'amount' => $balances['balance']
					];
				
				}
				
			}
			
			$this->response_raw = json_encode( $return );
			
			$this->response = $return;
			
			return $this->response;
		
		}
		
		
		
		// *** Send raw ['amount'] from ['wallet'] to ['destination'] ***
		
		
		
		public function wallet_send( array $args )
		{
			
			// Check all args
			
			if( !isset( $args['wallet'] ) || !isset( $args['destination'] ) || !isset( $args['amount'] ) )
			{
				return ['error'=>'Unable to parse Array'];
			}
			
			$wallet = $args['wallet'];
			
			$destination = $args['destination'];
			
			$amount = $args['amount'];
			
			// Wallet ok?
			
			$wallet_info = $this->wallet_info( ['wallet' => $wallet] );
			
			if( isset( $wallet_info['error'] ) )
			{
				return ['error'=>'Bad wallet number'];
			}
		
			// Destination ok?
		
			$check_destination = $this->validate_account_number( ['account'=>$destination] );
			
			if( $check_destination['valid'] != 1 )
			{
				return ['error'=>'Bad destination'];
			}
			
			// Amount ok?
			
			if( !ctype_digit( $amount ) )
			{
				return ['error'=>'Bad amount'];
			}
			
			if( gmp_cmp( $amount, 1 ) < 0 )
			{
				return ['error'=>'Bad amount'];
			}
			
			if( gmp_cmp( $wallet_info['balance'], $amount ) < 0 )
			{
				return ['error'=>'Insufficient balance'];
			}
			
			
			
			// Execution
		
		
		
			$return = ['balances' => []];
			
			$selected_accounts = [];
			
			$amount_left = $amount;
			
			// Get wallet balances
			
			$args =
			[
				'wallet' => $wallet,
				'threshold' => 1
			];
			
			$wallet_balances = $this->wallet_balances( $args );
				
			// Sort from higher to lower balance
			
			uasort( $wallet_balances['balances'], function( $a, $b )
			{
				
				return gmp_cmp( $b['balance'], $a['balance'] );
				
			});
			
			// Select accounts
			
			foreach( $wallet_balances['balances'] as $account => $balances )
			{
				
				if( gmp_cmp( $balances['balance'], $amount_left ) >= 0 )
				{
					
					$selected_accounts[$account] = $amount_left;
					
					$amount_left = '0';
					
				}
				else
				{
					
					$selected_accounts[$account] = $balances['balance'];
					
					$amount_left = gmp_strval( gmp_sub( $amount_left, $balances['balance'] ) );
					
				}
				
				if( gmp_cmp( $amount_left, '0' ) <= 0 )
				{
					break; // Amount reached
				}
				
			}

			// Send from selected accounts
			
			foreach( $selected_accounts as $account => $balance )
			{
				
				$args =
				[
					'wallet' => $wallet,
					'source' => $account,
					'destination' => $destination,
					'amount' => $balance,
					'id' => uniqid()
				];
				
				$send = $this->send( $args );

				if( $send['block'] != '0000000000000000000000000000000000000000000000000000000000000000' )
				{
				
					$return['balances'][$account] =
					[
						'block' => $send['block'],
						'amount' => $balance
					];
				
				}
				else
				{
					
					$return['error'] = 'Insufficient balance';
				
					$return['balances'][$account] =
					[
						'block' => '0000000000000000000000000000000000000000000000000000000000000000',
						'amount' => $balance
					];
				
				}
			
			}
			
			$this->response_raw = json_encode( $return );
			
			$this->response = $return;
			
			return $this->response;
			
		}
		
		
		
		// *** Generate keypair of account with ['string'] at ['position'] ***
		
		
		
		public function vanity_account( array $args )
		{
			
			// Check all args
			
			if( !isset( $args['string'] ) )
			{
				return ['error'=>'Unable to parse Array'];
			}
			
			$string = strtolower( $args['string'] );
			
			// Position ok?
			
			$position = isset( $args['position'] ) ? $args['position'] : 'start';
			
			// String ok?
			
			if( !ctype_alnum( $string ) || preg_match( '/[lv02]/', $string ) == 1 )
			{
				return ['error'=>'Bad string'];
			}
			
			if( strlen( $string ) < 1 )
			{
				return ['error'=>'Bad string'];
			}
			
			if( strlen( $string ) > 10 )
			{
				return ['error'=>'Long string'];
			}
			
			
			
			// Execution
			
			
			
			$i = 0; $a = 0; $start = time();

			do
			{
				
				$return = $this->key_create();
				
				$account = $return['account'];
				
				if( $position == 'start' )
				{

					if( strpos( $account, 'xrb_' . $string ) === 0 || strpos( $account, 'nano_' . $string ) === 0 || strpos( $account, 'xrb_1' . $string ) === 0 || strpos( $account, 'xrb_3' . $string ) === 0 || strpos( $account, 'nano_1' . $string ) === 0 || strpos( $account, 'nano_3' . $string ) === 0 )
					{
						$i = 1;
					}
					
				}
				else
				{
				
					if( substr_compare( $account, $string, -strlen( $string ) ) === 0 )
					{
						$i = 1;
					}
				
				}
				
				$a++;
				
			}while( $i != 1 );
			
			$end = time();
			
			$gap = $end - $start;

			$gap = ( $gap <= 0 ) ? 1 : $gap;
			
			$return['count'] = $a;
			
			$return['seconds'] = $gap;
			
			$return['aps'] = $a/$gap;
				
			$this->response_raw = json_encode( $return );
				
			$this->response = $return;
			
			return $this->response;
		
		}
	
	}
	
?>