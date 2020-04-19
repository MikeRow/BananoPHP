<?php

	// *****************
	// *** Libraries ***
	// *****************
	
	
	
	
	
	
	require_once __DIR__ . '/../lib/NanoTools.php';

	require_once __DIR__ . '/../lib3/phpseclib_loader.php';
	
	require_once __DIR__ . '/../lib3/clitable_loader.php';
	
	
	
	use php4nano\lib\NanoTools\NanoTools as NanoTools;
	
	use phpseclib\Crypt\RSA;
	
	use phpseclib\Net\SSH2;
	
	use jc21\CliTable;
	
	use jc21\CliTableManipulator;
	
	
	
	
	
	
	// *********************
	// *** Configuration ***
	// *********************
	
	
	
	
	
	
	define( 'data_dir'              , __DIR__ . '/data' );
	
	define( 'config_file'           , data_dir . '/config.json' );
	
	define( 'nodes_file'            , data_dir . '/nodes.json' );
	
	$C = []; // Configuration
	
	$C2 = []; // Secondary configuration
	
	
	
	// *** Create data folder if not exsist ***
	
	
	
	if( !is_dir( data_dir ) )
	{
		mkdir( data_dir );
	}

	
	
	// *** config.json model ***
	
	
	
	$C_model =
	[
		'nano' =>
		[
			'denomination' => 'NANO',
			'decimals'     => 3
		],
		'delay' => 100,
		'timezone' => 'UTC',
		'format' =>
		[
			'timestamp' => 'm/d/Y H:i:s',
			'decimal'   => '.',
			'thousand'  => ','
		]
	];
	
	
	
	// *** nodes.json model ***
	
	
	
	$node_model =
	[
		'hostname'  => '',
		'username'  => '',
		'password'  => '',
		'key_path'  => '',
		'auth_type' => '',
		'ncm_path'  => ''
	];
	
	
	
	// *** Load config.json ***
	
	
	
	if( !file_exists( config_file ) )
	{
		$C = $C_model;
	}
	else
	{
		
		$C = json_decode( file_get_contents( config_file ), true );
	
		// Check
		
		$C = array_merge_new_recursive( $C, $C_model );
		
	}
	
	
	
	// *** Load nodes.json ***
	
	
	
	if( !file_exists( nodes_file ) )
	{
		$C2['nodes']['tag'] = $node_model;
	}
	else
	{
		
		$C2['nodes'] = json_decode( file_get_contents( nodes_file ), true );
		
		// Check
		
		foreach( $C2['nodes'] as $tag => $node_data )
		{
			
			foreach( $node_model as $key => $value )
			{
				
				if( !array_key_exists( $key, $node_data ) )
				{
					$C2['nodes'][$tag][$key] = $value;
				}
				
			}

		}
	
	}
	
	
	
	// *** Set timezone ***
	
	
	
	date_default_timezone_set( $C['timezone'] );
		

	
	// *** Save config.json, nodes.json ***
	
	
	
	file_put_contents( config_file, json_encode( $C, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	
	file_put_contents( nodes_file, json_encode( $C2['nodes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	
	
	
	
	
	
	// *****************
	// *** Functions ***
	// *****************
	
	
	
	
	
	
	// *** Merge array2 to array1, only missing elements ***
	
	
	
	function array_merge_new_recursive( array $array1, array $array2 )
	{
		
		foreach( $array2 as $key => $value )
		{
			
			if( is_array( $value ) && isset( $array1[$key] ) && is_array( $array1[$key] ) )
			{
				$array1[$key] = array_merge_new_recursive( $array1[$key], $value );
			}
			else
			{
				
				if( !isset( $array1[$key] ) )
				{
					$array1[$key] = $value;
				}
				
			}
			
		}
		
		return $array1;
		
	}
	
	
	
	// *** Custom number format ***
	
	
	
	function custom_number( $number, $decimals = -1 )
	{
		
		global $C;
		
		global $C2;
		
		// $number = sprintf( "%s", $number );
		
		if( $decimals < 0 )
		{
		
			$amount_array = explode( '.', $number );
			
			if( isset( $amount_array[1] ) )
			{
		
				// Remove useless decimals
			
				while( substr( $amount_array[1], -1 ) == '0' )
				{
					$amount_array[1] = substr( $amount_array[1], 0, -1 );	
				}
				
				if( strlen( $amount_array[1] ) < 1 )
				{
					return number_format( $amount_array[0], 0, '', $C['format']['thousand'] );
				}
				else
				{
					return number_format( $amount_array[0], 0, '', $C['format']['thousand'] ) . '.' . $amount_array[1];
				}
			
			}
			else
			{
				return number_format( floor( $number ), 0, '', $C['format']['thousand'] );
			}
			
		}
		elseif( $decimals == 0 )
		{
			return number_format( floor( $number ), 0, $C['format']['decimal'], $C['format']['thousand'] );
		}
		else
		{
			return number_format( $number, $decimals, $C['format']['decimal'], $C['format']['thousand'] );
		}
	
	}
	
	
	
	// *** ncmCall ***
	
	
	
	function ncmCall( &$ssh, string $ncm_path, string $command, array $arguments, string $flags = '', string $callerID = 'remote-script' )
	{
		
		if( $flags != '' ) $flags .= ',';
		
		$flags .= 'json_in,json_out,no_confirm';
		
		$return = $ssh->exec( "php $ncm_path $command '" . json_encode( $arguments ) . "' flags=$flags callerID=$callerID" . PHP_EOL );
		
		return json_decode( $return, true );
		
	}
	
	
	
	
	
	
	// *****************
	// *** Get input ***
	// *****************
	
	
	
	
	
	
	if( count( $argv ) < 2 ) exit;
	
	$command = $argv[1];
	
	unset( $argv[0] );
	
	unset( $argv[1] );
	
	$argv = array_values( $argv );
	
	
	
	
	
	
	// *******************
	// *** Build input ***
	// *******************
	
	
	
	
	
	
	$arguments = [];
	
	$flags =
	[
		'json_out'    => false,
		'no_refresh'  => false
	];
	
	
	
	// *** Search for flags ***
	
	
	
	foreach( $argv as $index => $arg )
	{
		
		$arguments_row = explode( '=', $arg, 2 );
		
		if( $arguments_row[0] == 'flags' )
		{
			
			$input_flags = explode( ',', $arguments_row[1] );
			
			foreach( $input_flags as $input_flag )
			{
				
				if( array_key_exists( $input_flag, $flags ) )
				{
					$flags[$input_flag] = true;
				}
				
			}
			
			unset( $argv[$index] );
		
		}
		
	}
	


	// *** Arguments ***
	


	foreach( $argv as $arg )
	{
	
		$arguments_row = [];
		
		$arguments_row = explode( '=', $arg, 2 );
		
		if( !isset( $arguments_row[1] ) )
		{
			$arguments_row[1] = '';
		}
		
		$arguments[$arguments_row[0]] = $arguments_row[1];
		
	}
	
	
	
	
	
	
	// *****************
	// *** Execution ***
	// *****************
	
	
	
	
	
	
	if( $command == 'init' )
	{
		echo 'Init completed' . PHP_EOL;
	}
	else
	{
		
		$first_table_display = true;
		
		$last_update = microtime( true );
		
		$ncm_flags = 'raw_in,raw_out'; // ncmCall default flags
	
		$ncm_callerID = 'nco'; // ncmCall default callerID
		
		//
		
		while( true )
		{
		
			$table_data = [];
			
				
		
			// *** Get info from nodes ***
		
		
		
			foreach( $C2['nodes'] as $tag => $node_data )
			{
				
				$table_data[$tag] =
				[
					'tag'                            => $tag,
					'notice'                         => null,
					'node_version'                   => null,
					'node_uptime'                    => null,
					'node_blockchain'                => null,
					'node_block_average'             => null,
					'block_count'                    => null,
					'block_unchecked'                => null,
					'block_cemented'                 => null,
					'network_peers'                  => null,
					'network_representatives_online' => null,
					'network_weight_online'          => null,
					'network_weight_online_percent'  => null,
					'wallets_balance'                => null,
					'wallets_pending'                => null,
					'wallets_weight'                 => null,
					'wallets_count'                  => null,
					'wallets_accounts_count'         => null
				];
				
				
				
				// *** SSH connection ***
				
				
				
				$ssh = new SSH2( $node_data['hostname'] );
				
				if( $node_data['auth_type'] == 'key' )
				{
					
					$key = new RSA();
					
					$key->loadKey( file_get_contents( $node_data['key_path'] ) );
					
				}
				elseif( $node_data['auth_type'] == 'protected-key' )
				{
					
					$key = new Crypt_RSA();
					
					$key->setPassword( $node_data['password'] );
					
					$key->loadKey( file_get_contents( $node_data['key_path'] ) );
					
				}
				else
				{
					$key = $node_data['password'];
				}
				
				if( @!$ssh->login( $node_data['username'], $key ) )
				{
					
					$table_data[$tag]['notice'] = 'Failed SSH connection';
					
					$ssh->disconnect();
					
					continue;
					
				}
				
				
				
				// *** Call configuration ***
				
				
				
				$ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'version', [], $ncm_flags, $ncm_callerID );
	
				// Check for errors
	
				if( isset( $ncmCall['error'] ) )
				{
					$table_data[$tag]['notice'] = $ncmCall['error']; continue;
				}
				
				if( !isset( $ncmCall['node_vendor'] ) )
				{
					$table_data[$tag]['notice'] = 'Failed ncm call'; continue;
				}
				
				// Check for alerts
				
				if( isset( $ncmCall['alert'] ) )
				{
					$table_data[$tag]['notice'] = 'Alerts';
				}
				else
				{
					$table_data[$tag]['notice'] = 'OK';
				}	
					
				
				
				// *** Call to node ***
				
				
				
				if( $command == 'sync' )
				{
					
					$ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'block_count', [], $ncm_flags, $ncm_callerID );
					
					$table_data[$tag]['block_count'] = custom_number( $ncmCall['count'] );
					
					$table_data[$tag]['block_unchecked'] = custom_number( $ncmCall['unchecked'] );
					
					$table_data[$tag]['block_cemented'] = custom_number( $ncmCall['cemented'] );
						
						
					$ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'peers', [], $ncm_flags, $ncm_callerID );
					
					$table_data[$tag]['network_peers'] = custom_number( count( $ncmCall['peers'] ) );
						
						
					$ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'representatives_online', [], $ncm_flags, $ncm_callerID );
					
					$table_data[$tag]['network_representatives_online'] = custom_number( $ncmCall['count'] );
					
					$table_data[$tag]['network_weight_online'] = custom_number( NanoTools::raw2den( $ncmCall['weight'], $C['nano']['denomination'] ), $C['nano']['decimals'] );
					
					$table_data[$tag]['network_weight_online_percent'] = custom_number( $ncmCall['weight_percent'], 2 );
					
				}
				elseif( $command == 'wallets' )
				{
					
					$ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'wallet_list', [], $ncm_flags, $ncm_callerID );
					
					$wallets_balance = '0';
					
					$wallets_pending = '0';
					
					$wallets_weight = '0';
					
					$wallets_count = 0;
					
					$wallets_accounts_count = 0;
			
					foreach( $ncmCall as $wallet_id => $wallet_info )
					{
						
						$wallets_count++;
						
						$wallets_balance = gmp_add( $wallets_balance, $wallet_info['balance'] );
						
						$wallets_pending = gmp_add( $wallets_pending, $wallet_info['pending'] );
					
						$wallets_weight = gmp_add( $wallets_weight, $wallet_info['weight'] );
						
						$wallets_accounts_count += $wallet_info['accounts_count'];
					
					}
					
					$table_data[$tag]['wallets_balance'] = custom_number( NanoTools::raw2den( gmp_strval( $wallets_balance ), $C['nano']['denomination'] ), $C['nano']['decimals'] );
					
					$table_data[$tag]['wallets_pending'] = custom_number( NanoTools::raw2den( gmp_strval( $wallets_pending ), $C['nano']['denomination'] ), $C['nano']['decimals'] );
					
					$table_data[$tag]['wallets_weight'] = custom_number( NanoTools::raw2den( gmp_strval( $wallets_weight ), $C['nano']['denomination'] ), $C['nano']['decimals'] );
					
					$table_data[$tag]['wallets_count'] = custom_number( $wallets_count );
					
					$table_data[$tag]['wallets_accounts_count'] = custom_number( $wallets_accounts_count );
					
				}
				elseif( $command == 'node' )
				{
					
					$ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'version', [], $ncm_flags, $ncm_callerID );
					
					$table_data[$tag]['node_version'] = $ncmCall['node_vendor'];
					
					
					$ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'uptime', [], $ncm_flags, $ncm_callerID );
										
					$table_data[$tag]['node_uptime'] = custom_number( $ncmCall['seconds']/60/60, 3 ) . ' h';
					
					
					$ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'blockchain', [], $ncm_flags, $ncm_callerID );
										
					$table_data[$tag]['node_blockchain'] = custom_number( $ncmCall['blockchain']/1000000, 0 ) . ' MB';
					
					$table_data[$tag]['node_block_average'] = custom_number( $ncmCall['block_average'], 0 ) . ' B';
					
				}
				else
				{
					
					echo 'Unknown command' . PHP_EOL; 
					
					$ssh->disconnect();
					
					exit;
					
				}
			
			
			
				// *** SSH disconnection ***
			
			
			
				$ssh->disconnect();
			
			}
			
			
			
			// *** Output ***
			
			
			
			if( $flags['json_out'] )
			{
				echo json_encode( $table_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL;
			}
			else
			{
				
				// *** Create table ***
				
				
				
				$table = new CliTable;
			
				$table->setChars(
				[
					'top'          => ' ',
					'top-mid'      => ' ',
					'top-left'     => ' ',
					'top-right'    => ' ',
					'bottom'       => ' ',
					'bottom-mid'   => ' ',
					'bottom-left'  => ' ',
					'bottom-right' => ' ',
					'left'         => ' ',
					'left-mid'     => ' ',
					'mid'          => ' ',
					'mid-mid'      => ' ',
					'right'        => ' ',
					'right-mid'    => ' ',
					'middle'       => ' ',
				]);
			
				$table->setHeaderColor('cyan');
				
				
				
				// *** Set table fields ***
				
				
				
				$table->addField( 'Tag', 'tag', false );
				
				if( $command == 'sync' )
				{
					
					$table->addField( 'Blocks', 'block_count', false );
					
					$table->addField( 'Unchecked', 'block_unchecked', false );
					
					$table->addField( 'Cemented', 'block_cemented', false );
					
					$table->addField( 'Peers', 'network_peers', false );
					
					$table->addField( 'Reps.', 'network_representatives_online', false );
					
					$table->addField( 'Weight Online', 'network_weight_online', false );
					
					$table->addField( '%', 'network_weight_online_percent', false );
					
				}
				elseif( $command == 'wallets' )
				{
				
					$table->addField( 'Balance', 'wallets_balance', false );
					
					$table->addField( 'Pending', 'wallets_pending', false );
					
					$table->addField( 'Weight', 'wallets_weight', false );
					
					$table->addField( 'Count', 'wallets_count', false );
					
					$table->addField( 'Accounts', 'wallets_accounts_count', false );
				
				}
				elseif( $command == 'node' )
				{
					
					$table->addField( 'Version', 'node_version', false );
					
					$table->addField( 'Uptime', 'node_uptime', false );
					
					$table->addField( 'Blockchain', 'node_blockchain', false );
					
					$table->addField( 'Block', 'node_block_average', false );
					
				}
				else
				{
					//
				}
				
				$table->addField( 'Notice', 'notice', false );
				
				
				
				// *** Set table data ***
				
				
				
				$table->injectData( $table_data );
				
				
				
				// *** Output adjustments ***
				
				
				// Hide cursor
				
				fprintf( STDOUT, "\033[?25l" );
				
				// Clear screen
				
				if( $first_table_display )
				{
					
					// Clear all screen
					
					echo "\033[2J\033[;H";
					
					$first_table_display = false;
				
				}
				else
				{
					
					// Clear only last table
					
					echo "\033[" . strval( 5 + count( $table_data ) ) . "A";
					
				}
				
				// Print table
				
				$table->display();
				
				// Print other info
			
				echo ' delay: ' . custom_number( microtime( true ) - $last_update, 3 );
				
				echo ' | nodes: ' . count( $table_data );
				
				echo PHP_EOL;
				
				// Show cursor
				
				fprintf( STDOUT, " \033[?25h" );
				
			}
			
			if( $flags['no_refresh'] )
			{
				break;
			}
			
			$last_update = microtime( true );
			
			usleep( (int) $C['delay'] * 1000 );
			
		}
			
	}

?>