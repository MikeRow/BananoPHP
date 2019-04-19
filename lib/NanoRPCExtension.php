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
			
			// Check input
			
			if( !isset( $args['wallet'] ) || !isset( $args['destination'] ) )
			{
				return ['error'=>'Unable to parse Array'];
			}
			
			$wallet = $args['wallet'];
			
			$destination = $args['destination'];
			
			$wallet_info = $this->wallet_info( ['wallet' => $wallet] );
			
			if( isset( $wallet_info['error'] ) )
			{
				return ['error'=>'Bad wallet number'];
			}
			
			if( gmp_cmp( $wallet_info['balance'], 1 ) < 0 )
			{
				return ['error'=>'Insufficient balance'];
			}
			
			$check_destination = $this->validate_account_number( ['account'=>$destination] );
			
			if( $check_destination['valid'] != 1 )
			{
				return ['error'=>'Bad destination'];
			}
			
			// Execution
		
			$return = ['balances' => []];
			
			$args =
			[
				'wallet' => $wallet,
				'threshold' => 1
			];
			
			$wallet_balances = $this->wallet_balances( $args ); // Get wallet balances
			
			foreach( $wallet_balances['balances'] as $account => $balances )
			{
				
				if( gmp_cmp( $balances['balance'], 0 ) > 0 )
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
					
						$return['balances'][$account] =
						[
							'block' => '0000000000000000000000000000000000000000000000000000000000000000',
							'amount' => $balances['balance']
						];
					
					}
				
				}
				
			}
			
			$this->response_raw = json_encode( $return );
			
			$this->response = $return;
			
			return $this->response;
		
		}
		
		
		
		// *** Send raw ['amount'] from ['wallet'] to ['destination'] ***
		
		
		
		public function wallet_send( array $args )
		{
			
			// Check input
			
			if( !isset( $args['wallet'] ) || !isset( $args['destination'] ) || !isset( $args['amount'] ) )
			{
				return ['error'=>'Unable to parse Array'];
			}
			
			$wallet = $args['wallet'];
			
			$destination = $args['destination'];
			
			$amount = $args['amount'];
			
			$wallet_info = $this->wallet_info( ['wallet' => $wallet] );
			
			if( isset( $wallet_info['error'] ) )
			{
				return ['error'=>'Bad wallet number'];
			}
		
			$check_destination = $this->validate_account_number( ['account'=>$destination] );
			
			if( $check_destination['valid'] != 1 )
			{
				return ['error'=>'Bad destination'];
			}
			
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
			
			$sum = 0;
			
			$diff_amount = $amount;
			
			$args =
			[
				'wallet' => $wallet,
				'threshold' => 1
			];
			
			$wallet_balances = $this->wallet_balances( $args ); // Get wallet balances
			
			// Select funds from accounts
			
			foreach( $wallet_balances['balances'] as $account => $balances )
			{
			
				if( gmp_cmp( $balances['balance'], 0 ) > 0 )
				{
				
					$selected_accounts[$account] = $balances['balance'];
					
					$sum = gmp_add( $sum, $balances['balance'] );
				
				}
				else
				{
					continue;
				}
				
				if( gmp_cmp( $sum, $amount ) >= 0 )
				{
					break; // Amount reached
				}
			
			}
			
			// Amount not reached
			
			if( gmp_cmp( $sum, $amount ) < 0 )
			{
			
				$return['amount'] = 0;
				
				$return['status'] = 0;
				
				return ['error'=>'Insufficient balance'];
			
			}
			
			// Amount reached
			
			foreach( $selected_accounts as $selected_account => $balance ){
				
				if( gmp_cmp( $diff_amount, $balance ) <= 0 )
				{
					$balance = gmp_strval( $diff_amount );
				}
				
				$args =
				[
					'wallet' => $wallet,
					'source' => $selected_account,
					'destination' => $destination,
					'amount' => $balance,
					'id' => uniqid()
				];
				
				$send = $this->send( $args );
				
				if( $send['block'] != '0000000000000000000000000000000000000000000000000000000000000000' )
				{
				
					$return['balances'][$selected_account] =
					[
						'block' => $send['block'],
						'amount' => $balance
					];
					
					$diff_amount = gmp_sub( $diff_amount, $balance );
				
				}
				else
				{
				
					$return['balances'][$selected_account] =
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
		
		
		
		// *** Generate keypair of account starting or ending with ['string'] ***
		
		
		
		public function vanity_account( array $args )
		{
			
			// Check input
			
			if( !isset( $args['string'] ) )
			{
				return ['error'=>'Unable to parse Array'];
			}
			
			$string = strtolower( $args['string'] );
			
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
				
				if( substr_compare( $account, $string, -strlen( $string ) ) === 0 || strpos( $account, 'xrb_' . $string ) === 0 || strpos( $account, 'nano_' . $string ) === 0 || strpos( $account, 'xrb_1' . $string ) === 0 || strpos( $account, 'xrb_3' . $string ) === 0 || strpos( $account, 'nano_1' . $string ) === 0 || strpos( $account, 'nano_3' . $string ) === 0 )
				{
					$i = 1;
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