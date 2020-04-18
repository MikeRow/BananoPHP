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
		'delay' => 1,
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
		'hostname'  => 'SSH hostname',
		'username'  => 'SSH username',
		'password'  => 'SSH password',
		'key_path'  => 'SSH private key path',
		'auth_type' => 'password,key,protected-key',
		'ncm_path'  => '/home/nano/php4nano/ncm/ncm.php'
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
	
	
	
	function custom_number( string $number, int $decimals = -1 )
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
	elseif( in_array( $command, ['sync','wallets','general'] ) )
	{
		
		$first_table_display = true;
		
		$last_update = time();
		
		//
		
		while( true )
		{
			
			$table_data = [];
			
			$nodes_data = [];
		
		
		
			// *** Get nodes info ***
		
		
		
			foreach( $C2['nodes'] as $tag => $node_data )
			{
				
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
					
				}
				else
				{
					$key = $node_data['password'];
				}
				
				if( @!$ssh->login( $node_data['username'], $key ) )
				{
					
					$nodes_data[$tag]['error'] = 'SSH failed';
					
					$ssh->disconnect();
					
					continue;
					
				}
				
				$nodes_data[$tag] = json_decode( $ssh->exec( "php " . $node_data['ncm_path'] . " status flags=raw_in,json_in,raw_out,json_out callerID=nco" . PHP_EOL ), true );
			
				$ssh->disconnect();
			
			}
			
			
			
			// *** Set comparison variables ***
			
			/*
			
			$average_blocks_count = 0;
			
			$average_peers = 0;
			
			$average_representatives_online = 0;
			
			$average_weight_online = '0';
			
			$nodes = 0;
			
			
			foreach( $nodes_data as $tag => $node_data )
			{
				
				if( isset( $node_data['error'] ) ) continue;
				
				if( !isset( $node_data['node']['version'] ) ) continue;
				
				$average_blocks_count += $node_data['blocks']['count'];
				
				$average_peers = $node_data['network']['peers'];
				
				$average_representatives_online = $node_data['network']['representatives_online'];
				
				$average_weight_online = gmp_add( $average_weight_online, $node_data['network']['weight_online'] );
				
				$nodes++;
				
			}
			
			if( $nodes == 0 ) $nodes = 1;
			
			$average_blocks_count /= $nodes;
			
			$average_peers /= $nodes;
			
			$average_representatives_online /= $nodes;
			
			$average_weight_online = gmp_strval( gmp_div_q( $average_weight_online, strval( $nodes ) ) );
			
			*/
			
			// *** Build table data ***
			
			
			
			foreach( $nodes_data as $tag => $node_data )
			{
				
				// any error?
				
				if( isset( $node_data['error'] ) || !isset( $node_data['node']['version'] ) )
				{
					
					if( isset( $node_data['error'] ) )
					{
						$error = $node_data['error'];
					}
					elseif( !isset( $node_data['node']['version'] ) )
					{
						$error = 'ncm failed';
					}
					else
					{
						$error = 'Unknown error';
					}
					
					$table_data[] =
					[
						'tag'                            => $tag,
						'notice'        		         => $error,
						'node_version'                   => null,
						'node_uptime'                    => null,
						'node_blockchain'                => null,
						'blocks_count'                   => null,
						'blocks_unchecked'               => null,
						'blocks_cemented'                => null,
						'blocks_size_average'            => null,
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
					
				}
				
				// all ok
				
				else
				{
					
					if( isset( $node_data['alert'] ) )
					{
						$notice = 'Alerts';
					}
					else
					{
						$notice = 'OK';
					}
					
					$table_data[] =
					[
						'tag'                            => $tag,
						'notice'                         => $notice,
						'node_version'                   => $node_data['node']['version'],
						'node_uptime'                    => custom_number( $node_data['node']['uptime']/60/60, 2) . ' h',
						'node_blockchain'                => custom_number( $node_data['node']['blockchain']/1000000, 0) . ' MB',
						'blocks_count'                   => custom_number( $node_data['blocks']['count'] ),
						'blocks_unchecked'               => custom_number( $node_data['blocks']['unchecked'] ),
						'blocks_cemented'                => custom_number( $node_data['blocks']['cemented'] ),
						'blocks_size_average'            => custom_number( $node_data['blocks']['size_average'] ) . ' B',
						'network_peers'                  => custom_number( $node_data['network']['peers'] ),
						'network_representatives_online' => custom_number( $node_data['network']['representatives_online'] ),
						'network_weight_online'          => custom_number( NanoTools::raw2den( $node_data['network']['weight_online'], $C['nano']['denomination'] ), $C['nano']['decimals'] ), 
						'network_weight_online_percent'  => custom_number( $node_data['network']['weight_online_percent'], 2 ),
						'wallets_balance'                => custom_number( NanoTools::raw2den( $node_data['wallets']['balance'], $C['nano']['denomination'] ), $C['nano']['decimals'] ),
						'wallets_pending'                => custom_number( NanoTools::raw2den( $node_data['wallets']['pending'], $C['nano']['denomination'] ), $C['nano']['decimals'] ),
						'wallets_weight'                 => custom_number( NanoTools::raw2den( $node_data['wallets']['weight'], $C['nano']['denomination'] ), $C['nano']['decimals'] ),
						'wallets_count'                  => custom_number( $node_data['wallets']['count'] ),
						'wallets_accounts_count'         => custom_number( $node_data['wallets']['accounts_count'] )
					];
				
				}
				
			}
			
			
			
			// *** Output ***
			
			
			
			if( $flags['json_out'] )
			{
				echo json_encode( $table_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL;
			}
			else
			{
				
				// Create table
				
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
			
				// $table->setTableColor('blue');
			
				$table->setHeaderColor('cyan');
				
				// Set table fields
				
				$table->addField( 'Tag', 'tag', false );
				
				$table->addField( 'Notice', 'notice', false );
				
				if( $command == 'wallets' )
				{
					
					$table->addField( 'Balance', 'wallets_balance', false );
					
					$table->addField( 'Pending', 'wallets_pending', false );
					
					$table->addField( 'Weight', 'wallets_weight', false );
					
					$table->addField( 'Count', 'wallets_count', false );
					
					$table->addField( 'Accounts', 'wallets_accounts_count', false );
					
				}
				elseif( $command == 'sync' )
				{
					
					$table->addField( 'Blocks', 'blocks_count', false );
					
					$table->addField( 'Unchecked', 'blocks_unchecked', false );
					
					$table->addField( 'Cemented', 'blocks_cemented', false );
					
					$table->addField( 'Peers', 'network_peers', false );
					
					$table->addField( 'Reps.', 'network_representatives_online', false );
					
					$table->addField( 'Weight Online', 'network_weight_online', false );
					
					$table->addField( '%', 'network_weight_online_percent', false );
					
				}
				else
				{
					
					$table->addField( 'Version', 'node_version', false );
					
					$table->addField( 'Uptime', 'node_uptime', false );
					
					$table->addField( 'Blockchain', 'node_blockchain', false );
					
					$table->addField( 'Block', 'blocks_size_average', false );
					
				}
				
				// Set table data
				
				$table->injectData( $table_data );
				
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
			
				echo ' delay: ' . ( time() - $last_update );
				
				echo ' | nodes: ' . count( $table_data );
				
				echo PHP_EOL;
				
				// Show cursor
				
				fprintf( STDOUT, " \033[?25h" );
				
			}
			
			if( $flags['no_refresh'] )
			{
				break;
			}
			
			$last_update = time();
			
			sleep( (int) $C['delay'] );
			
		}
			
	}
	else
	{
		echo 'Unknown command' . PHP_EOL;
	}

?>