<?php

	// *****************
	// *** Libraries ***
	// *****************
	
	
	
	
	
	
	require_once __DIR__ . '/../lib/NanoTools.php';
	
	require_once __DIR__ . '/../lib/NanoCLI.php';
	
	require_once __DIR__ . '/../lib/NanoRPC.php';
	
	require_once __DIR__ . '/../lib/NanoRPCExtension.php';
	
	
	
	use php4nano\lib\NanoTools\NanoTools as NanoTools;
	
	
	
	
	
	
	// *********************
	// *** Configuration ***
	// *********************
	
	
	
	
	
	
	define( 'data_dir'              , __DIR__ . '/data' );
	
	define( 'log_dir'               , __DIR__ . '/log' );
	
	define( 'config_file'           , data_dir . '/config.json' );
	 
	define(	'ticker_file'           , data_dir . '/ticker.json' );
	
	define( 'tags_file'             , data_dir . '/tags.json' );    
	
	define( 'tags3_file'            , data_dir . '/tags3.json' );
	
	define( 'tabulation'            , '    ' );
	
	define( 'available_supply'      , '133248061996216572282917317807824970865' );
	
	$C = []; // Primary configuration
	
	$C2 = []; // Secondary configuration
	
	$call_return = []; // Output

	
	
	// *** Create data folder if not exsist ***
	
	
	
	if( !is_dir( data_dir ) )
	{
		mkdir( data_dir );
	}
	
	
	
	// *** Create log folder if not exsist ***
	
	
	
	if( !is_dir( log_dir ) )
	{
		mkdir( log_dir );
	}

	
	
	// *** config.json model ***
	
	
	
	$C_model =
	[
		'nano' =>
		[
			'denomination' => 'NANO',
			'node_file'    => '/home/nano/nano_node',
			'data_dir'     => '/home/nano/Nano',
			'connection'   => 'rpc',
			'rpc'          =>
			[
				'host' => 'localhost',
				'port' => '7076'
			]
		],
		'log' =>
		[
			'save'       => true,
			'privacy'    => true,
			'expiration' => 7
		],
		'timezone' => 'UTC',
		'format' =>
		[
			'timestamp' => 'm/d/Y H:i:s',
			'decimal'   => '.',
			'thousand'  => ','
		],
		'ticker' =>
		[
			'enable'            => false,
			'fav_vs_currencies' => 'BTC,USD'
		],
		'tags' =>
		 [
			'view'      => true,
			'separator' => '||'
		],
		'tags3' =>
		[
			'enable' => false
		]
	];
	
	
	
	// *** tags.json model ***
	
	
	
	$tags_model =
	[
		'account' =>
		[
			'genesis' => 'nano_3t6k35gi95xu6tergt6p69ck76ogmitsa8mnijtpxm9fkcm736xtoncuohr3'
		],
		'block' =>
		[
			'genesis' => '991CF190094C00F0B68E2E5F75F6BEE95A2E0BD93CEAA4A6734DB9F19B728948'
		],
		'wallet' =>
		[]
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
	
	
	
	// *** Load tags.json ***
	
	
	
	if( !file_exists( tags_file ) )
	{
		$C2['tags'] = $tags_model;
	}
	else
	{
		
		$C2['tags'] = json_decode( file_get_contents( tags_file ), true );
		
		// Check
		
		foreach( $tags_model as $section => $data )
		{
			if( !array_key_exists( $section, $C2['tags'] ) )
			{
				$C2['tags'][$section] = [];
			}
			
		}
	
	}
	
	
	
	// *** Set timezone ***
	
	
	
	date_default_timezone_set( $C['timezone'] );
	
	
	
	// *** Get ticker ***
	
	
	
	if( $C['ticker']['enable'] )
	{
		
		$ticker_array = json_decode( file_get_contents( ticker_file ), true );
		
		$C2['vs_currencies'] = $ticker_array['nano'];
		
		$C2['ticker_last'] = $ticker_array['last_updated_at'];
		
	}
	
	
	
	// *** Get tags3 ***
	
	
	
	if( $C['tags3']['enable'] )
	{
		$C2['tags3'] = json_decode( file_get_contents( tags3_file ), true );
	}
	
	
	
	// *** Save config.json, tags.json ***
	
	
	
	file_put_contents( config_file, json_encode( $C, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	
	file_put_contents( tags_file, json_encode( $C2['tags'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	
	
	
	


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
	
	
	
	// *** Sort array by key recursively ***
	
	
	
	function ksort_recursive( array &$array )
	{
		
		if( is_array( $array ) )
		{
			
			ksort( $array );
			
			array_walk( $array, 'ksort_recursive' );
			
		}
		
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
	
	
	
	// *** Pretty print_r ***
	
	
	
	function pretty_print_r( array $array, int $level = 1 )
	{
		
		$output = '';
		
		foreach( $array as $key => $value )
		{
			
			if( !is_array( $key ) )
			{
				$key = sprintf( "%s", $key );
			}
			
			if( !is_array( $value ) )
			{
				$value = sprintf( "%s", $value );
			}
			
			if( is_array( $value ) )
			{
				
				// It is an array
				
				$output .= str_repeat( tabulation, $level );
				
				$output .= '['.$key.'] =>' . PHP_EOL;
				
				$output .= pretty_print_r( $value, $level + 1 );
				
			}
			else
			{
				
				// It is not an array
				
				$output .= str_repeat( tabulation, $level );
				
				if( !ctype_digit( $key ) )
				{
					$output .= '['.$key.'] => ' . $value;
				}
				else
				{
					$output .= $value;
				}
				
				$output .= PHP_EOL;
				
			}
			
		}
		
		return $output;
		
	}
	
	
	
	// *** Tag filter ***
	
	
	
	function tag_filter( string $value )
	{
		
		$value = preg_replace( '/[^a-z0-9_. ]+/i', '', $value );
	
		$value = str_replace( ' ', '-', $value );
		
		$value = str_replace( '_', '-', $value );
		
		$value = strtolower( $value );
		
		return $value;
		
	}
	
	
	
	// *** Tag to value ***
	
	
	
	function tag2value( string $key, string $value )
	{
	
		global $C;
		
		global $C2;
	
		// Check if a wallet tag is available
		
		$check_words = ['wallet'];
		
		if( in_array( $key, $check_words ) )
		{
			
			if( array_key_exists( $value, $C2['tags']['wallet'] ) )
			{
				return $C2['tags']['wallet'][$value];
			}
			
		}
		
		// Check if an account tag is available
		
		$check_words = 
		[
			'account',
			'destination',
			'representative',
			'source'
		];
		
		if( in_array( $key, $check_words ) )
		{
			
			if( array_key_exists( $value, $C2['tags']['account'] ) )
			{
				return $C2['tags']['account'][$value];
			}
			elseif( $C['tags3']['enable'] && array_key_exists( $value, $C2['tags3']['account'] ) )
			{
				return $C2['tags3']['account'][$value];
			}
			else
			{}
			
		}
		
		// Check if an block tag is available
		
		$check_words = ['hash'];
		
		if( in_array( $key, $check_words ) )
		{
			
			if( array_key_exists( $value, $C2['tags']['block'] ) )
			{
				return $C2['tags']['block'][$value];
			}
			
		}
		
		return $value;
		
	}
	
	
	
	// *** Value to tag ***
	
	
	
	function value2tag( $value )
	{
		
		global $C;
		
		global $C2;
		
		if( !$C['tags']['view'] ) return $value;
		
		if( is_array( $value ) ) return $value;
		
		$key_check = explode( '_', $value );
	
		if( array_search( $value, $C2['tags']['wallet'] ) ) // Find a wallet tag
		{
			return array_search( $value, $C2['tags']['wallet'] ) . $C['tags']['separator'] . $value;
		}
		elseif( isset( $key_check[1] ) && ( $key_check[0] == 'xrb' || $key_check[0] == 'nano' ) ) // Find an account tag
		{

			if( array_search( 'xrb_' . $key_check[1], $C2['tags']['account'] ) )
			{
				return array_search( 'xrb_' . $key_check[1], $C2['tags']['account'] ) . $C['tags']['separator'] . $value;
			}
			elseif( array_search( 'nano_' . $key_check[1], $C2['tags']['account'] ) )
			{
				return array_search( 'nano_' . $key_check[1], $C2['tags']['account'] ) . $C['tags']['separator'] . $value;
			}
			elseif( $C['tags3']['enable'] && array_search( 'xrb_' . $key_check[1], $C2['tags3']['account'] ) )
			{
				return array_search( 'xrb_' . $key_check[1], $C2['tags3']['account'] ) . $C['tags']['separator'] . $value;
			}
			elseif( $C['tags3']['enable'] && array_search( 'nano_' . $key_check[1], $C2['tags3']['account'] ) )
			{
				return array_search( 'nano_' . $key_check[1], $C2['tags3']['account'] ) . $C['tags']['separator'] . $value;
			}
			else
			{
				return $value;
			}
		
		}
		elseif( array_search( $value, $C2['tags']['block'] ) ) // Find a block tag
		{
			return array_search( $value, $C2['tags']['block'] ) . $C['tags']['separator'] . $value;
		}
		else
		{
			return $value;
		}
		
	}
	
	
	
	// *** Elaborate output ***
	
	
	
	function eleborate_output( array $array )
	{
		
		global $C;
		
		global $C2;
		
		global $command;
		
		foreach( $array as $key => $value )
		{	

			if( !is_array( $key ) )
			{
				$key = sprintf( "%s", $key );
			}
			
			if( !is_array( $value ) )
			{
			
				// Bool format
				
				if( is_bool( $value ) || $key == 'locked' )
				{
					
					if( $value == true )
					{
						$array[$key] = 'true';
					}
					
					if( $value == false )
					{
						$array[$key] = 'false';
					}
					
				}
			
				$value = sprintf( "%s", $value );
			
			}
			
			// It is an array
			
			if( is_array( $value ) )
			{
				
				unset( $array[$key] );
				
				$key = value2tag( $key );
				
				$array[$key] = eleborate_output( $value );
				
			}
			
			// It is not an array but it's a encoded json
			
			elseif( !is_array( $value ) && $key == 'contents' )
			{
			
				$array[$key] = eleborate_output( json_decode( $value, true ) );
			
			}
			
			// It is not an array
			
			else
			{
				
				// Amount format
				
				$check_words = 
				[
					'amount',
					'available',
					'balance',
					'balance_cumulative',
					'online_stake_total',
					'online_weight_minimum',
					'peers_stake_required',
					'peers_stake_total',
					'pending',
					'quorum_delta',
					'receive_minimum',
					'vote_minimum',
					'weight',
					'weight_cumulative',
					'weight_online'
				];
			
				if( in_array( $key, $check_words ) && is_numeric( $value ) )
				{
				
					$array[$key] = custom_number( NanoTools::raw2den( $value, $C['nano']['denomination'] ) ) . ' ' . $C['nano']['denomination'];
					
					// If ticker is enabled shows amounts in favourite vs currencies
					
					if( $C['ticker']['enable'] )
					{
						
						$array[$key] = [];
						
						$array[$key][] = custom_number( NanoTools::raw2den( $value, $C['nano']['denomination'] ) ) . ' ' . $C['nano']['denomination'];
					
						$fav_vs_currencies = explode( ',', $C['ticker']['fav_vs_currencies'] );
					
						foreach( $fav_vs_currencies as $fav_vs_currency )
						{
						
							if( isset( $C2['vs_currencies'][strtoupper( $fav_vs_currency )] ) )
							{
								$array[$key][] = custom_number( number_format( NanoTools::raw2den( $value, 'NANO' ) * $C2['vs_currencies'][strtoupper( $fav_vs_currency )], 8, '.', '' ) ) . ' ' . strtoupper( $fav_vs_currency );
							}
							
						}
						
					}
					
				}
				
				// Date format
				
				$check_words = 
				[
					'local_timestamp',
					'modified_timestamp',
					'time',
					'timestamp'
				];
			
				if( in_array( $key, $check_words ) && is_numeric( $value ) )
				{
					$array[$key] = date( $C['format']['timestamp'], $value );
				}

				// Duration format
				
				$check_words = 
				[
					'seconds',
					'stat_duration_seconds'
				];
			
				if( in_array( $key, $check_words ) && is_numeric( $value ) )
				{
					$array[$key] = custom_number( $value, 0 ) . ' s';
				}
				
				if( $key == 'duration' && is_numeric( $value ) ) $array[$key] = custom_number( $value, 0 ) . ' ms';
				
				if( $key == 'uptime'   && is_numeric( $value ) ) $array[$key] = custom_number( $value / 3600, 2 ) . ' h';
				
				// Duration exceptions
				
				$check_words = 
				[
					'bootstrap_status'
				];
				
				if( in_array( $command, $check_words ) )
				{
					$array['duration'] = custom_number( $value, 0 ) . ' s';
				}
				
				// Default numeric format
				
				$check_words = 
				[
					'accounts',
					'accounts_count',
					'adhoc_count',
					'aps',
					'average',
					'blocks',
					'block_count',
					'block_processor_batch_max_time',
					'bootstrap_connections',
					'bootstrap_connections_max',
					'bootstrap_fraction_numerator',
					'cemented',
					'chain_request_limit',
					'change',
					'clients',
					'confirmation_height',
					'connections',
					'count',
					'deterministic_count',
					'deterministic_index',
					'difference',
					'frontier_request_limit',
					'height',
					'idle',
					'io_threads',
					'io_timeout',
					'lazy_state_unknown',
					'lazy_balances',
					'lazy_pulls',
					'lazy_stopped',
					'lazy_keys',
					'lmdb_max_dbs',
					'max_json_depth',
					'network_threads',
					'number',
					'online_weight_quorum',
					'open',
					'password_fanout',
					'peers',
					'pulls',
					'pulling',
					'receive',
					'reference',
					'restored_count',
					'send',
					'signature_checker_threads',
					'size',
					'state',
					'target_connections',
					'threads',
					'total_blocks',
					'work_threads',
					'unchecked',
					'unchecked_cutoff_time'
				];
			
				if( in_array( $key, $check_words ) && is_numeric( $value ) )
				{
					$array[$key] = custom_number( $value, 0 );
				}
				
				// Size format
				
				$check_words = 
				[
					'max_size',
					'rotation_size',
					'size'
				];
			
				if( in_array( $key, $check_words ) && is_numeric( $value ) )
				{
					$array[$key] = custom_number( $value/1000000, 0 ) . ' MiB';
				}
				
				$check_words = 
				[
					'blockchain'
				];
				
				if( in_array( $key, $check_words ) && is_numeric( $value ) )
				{
					$array[$key] = custom_number( $value/1000000, 0 ) . ' MB';
				}
				
				$check_words = 
				[
					'size_average'
				];
			
				if( in_array( $key, $check_words ) && is_numeric( $value ) )
				{
					$array[$key] = custom_number( $value, 0 ) . ' B';
				}
					
				// Error format
				
				if( $key == 'error' && $value == 'Unable to parse JSON' ) $array[$key] = 'Bad call';
				
				if( $key == 'error' && $value == 'Unable to parse Array' ) $array[$key] = 'Bad call';
				
				// Tag replacement
				
				$array[$key] = value2tag( $array[$key] );
				
			}
			
		}
		
		return $array;
		
	}
	
	
	
	
	
	
	// ************************
	// *** Node connections ***
	// ************************
	
	
	
	
	
	
	// Node CLI
	
	$nanocli = new php4nano\lib\NanoCLI\NanoCLI( $C['nano']['node_file'] );
	
	// Node call
	
	if( $C['nano']['connection'] == 'rpc' )
	{
		$nanocall = new php4nano\lib\NanoRPCExtension\NanoRPCExtension( $C['nano']['rpc']['host'], $C['nano']['rpc']['port'] );

	}
	
	// Node call check
	
	$check_words = ['rpc'];
			
	if( in_array( $C['nano']['connection'], $check_words ) )
	{
		$check_node_connection = $nanocall->version();
	}
	else
	{
		$check_node_connection = false;
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
		'raw_in'          => false,
		'raw_out'         => false,
		'json_in'         => false,
		'json_out'        => false,
		'call'            => false,
		'cli'             => false,
		'no_confirm'      => false,
		'no_log'          => false
	];
	
	$callerID = 'default';
	
	$alerts = [];
	
	
	
	// *** Search for flags and callerID ***
	
	
	
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
		
		if( $arguments_row[0] == 'callerID' )
		{
			
			if( strlen( $arguments_row[1] ) > 0 )
			{
				$callerID = $arguments_row[1];
			}
			
			unset( $argv[$index] );
		
		}
		
	}
	
	if( $flags['call'] && $flags['cli'] )
	{
		$flags['cli'] = false;
	}
	
	
	
	// *** Json/Default input ***
	
	
	
	if( $flags['json_in'] )
	{
		
		if( count( $argv ) > 0 )
		{
			$arguments = json_decode( $argv[0], true );
		}
	
	}
	else
	{
		
		foreach( $argv as $arg )
		{
		
			$arguments_row = [];
			
			$arguments_row = explode( '=', $arg, 2 );
			
			if( !isset( $arguments_row[1] ) )
			{
				$arguments_row[1] = '';
			}
		
			// Elaborate accounts array

			$check_words = ['accounts'];
			
			if( in_array( $arguments_row[0], $check_words ) )
			{
				$arguments_row[1] = explode( ',', $arguments_row[1] );
			}
			
			// Elaborate blocks array
			
			$check_words = ['hashes'];
			
			if( in_array( $arguments_row[0], $check_words ) )
			{
				$arguments_row[1] = explode( ',', $arguments_row[1] );
			}
			
			$arguments[$arguments_row[0]] = $arguments_row[1];
			
		}
		
	}
	
	
	
	// *** Default/Raw input ***
	
	
	
	if( !$flags['raw_in'] )
	{
	
		foreach( $arguments as $argument0 => $argument1 )
		{
			
			// Check for tags in account array

			$check_words = ['accounts'];
			
			if( in_array( $argument0, $check_words ) )
			{
			
				foreach( $argument1 as $key => $value )
				{
					$arguments[$argument0][$key] = tag2value( 'account', $value );
				}
				
			}
			
			// Check for tags in block array
			
			$check_words = ['hashes'];
			
			if( in_array( $argument0, $check_words ) )
			{
			
				foreach( $argument1 as $key => $value )
				{
					$arguments[$argument0][$key] = tag2value( 'block', $value );
				}
				
			}
			
			// Convert amount to raw
			
			$check_words =
			[
				'amount',
				'balance_min',
				'balance_max',
				'weight_min',
				'weight_max'
			];
			
			if( in_array( $argument0, $check_words ) )
			{
				
				if( !is_numeric( $argument1 ) )
				{
				
					$input_currency = explode( '-', $argument1 );
					
					$input_currency[0] = str_replace( '-', '', $input_currency[0] );
					
					// raw input
					
					if( is_numeric( $input_currency[0] ) && isset( $input_currency[1] ) && $input_currency[1] == 'raw' )
					{
						$arguments[$argument0] = $input_currency[0];
					}
					
					// denomination input
					
					elseif( is_numeric( $input_currency[0] ) && isset( $input_currency[1] ) && isset( NanoTools::raw2[$input_currency[1]] ) )
					{
						$arguments[$argument0] = NanoTools::den2raw( $input_currency[0], $input_currency[1] );
					}
					
					// ticker input
					
					elseif( is_numeric( $input_currency[0] ) && isset( $input_currency[1] ) && $C['ticker']['enable'] && isset( $C2['vs_currencies'][strtoupper( $input_currency[1] )] ) )
					{
						$arguments[$argument0] = NanoTools::den2raw( $input_currency[0] / $C2['vs_currencies'][strtoupper( $input_currency[1] )], 'NANO' );
					}
					
					// unknown input
					
					else
					{
						$arguments[$argument0] = '0';
					}
					
				}
				else
				{
					
					$argument1 = str_replace( '-', '', $argument1 );
					
					// default denomination input
					
					if( is_numeric( $argument1 ) )
					{
						$arguments[$argument0] = NanoTools::den2raw( $argument1, $C['nano']['denomination'] );
					}
					
					// unknown input
					
					else
					{
						$arguments[$argument0] = '0';
					}
					
				}
				
			}
			
			// Check for tags
			
			else
			{
				$arguments[$argument0] = tag2value( $argument0, $argument1 );
			}
			
			// Generate automatic unique id for send command
			
			if( $command == 'send' && $argument0 == 'id' && $argument1 == 'uniqid' )
			{
				$arguments[$argument0] = uniqid();
			}
		
		}

	}
	
	
	
	
	
	
	// **************************************
	// *** Confirmation if sending amount ***
	// **************************************
	
	
	
	
	
	
	if( !$flags['no_confirm'] )
	{

		$check_words = ['send','wallet_wipe','wallet_send'];
			
		if( in_array( $command, $check_words ) )
		{
		
			// send call
		
			if( $command == 'send' )
			{
			
				if( isset( $arguments['wallet'] ) && isset( $arguments['source'] ) && isset( $arguments['destination'] ) && isset( $arguments['amount'] ) )
				{
					
					$confirmation_amount = $arguments['amount'];
					
				}
				else
				{
					$confirmation_amount = 0;
					
				}
			
			}
			
			// wallet_send call
			
			elseif( $command == 'wallet_send' )
			{
			
				if( isset( $arguments['wallet'] ) && isset( $arguments['destination'] ) && isset( $arguments['amount'] ) )
				{
					
					$wallet_info = $nanocall->wallet_info( ['wallet'=>$arguments['wallet']] );
					
					if( !isset( $wallet_info['error'] ) )
					{
						$confirmation_amount = $arguments['amount'];
					}
					else
					{
						$confirmation_amount = 0;
					}
					
				}
				else
				{
					$confirmation_amount = 0;
					
				}
			
			}
			
			// wallet_wipe call
			
			elseif( $command == 'wallet_wipe' )
			{
			
				if( isset( $arguments['wallet'] ) && isset( $arguments['destination'] ) )
				{
					
					$wallet_info = $nanocall->wallet_info( ['wallet'=>$arguments['wallet']] );
					
					if( !isset( $wallet_info['error'] ) )
					{
						$confirmation_amount = $wallet_info['balance'];
					}
					else
					{
						$confirmation_amount = 0;
					}
					
				}
				else
				{
					$confirmation_amount = 0;
					
				}
			
			}
			
			// Impossible
			
			else
			{
				$confirmation_amount = 0;
			}
			
			// Confirmation
			
			if( $confirmation_amount != 0 )
			{
				
				$confirmation_amount = custom_number( NanoTools::raw2den( $confirmation_amount, $C['nano']['denomination'] ) ) . ' ' . $C['nano']['denomination'];
				
				echo PHP_EOL . 'Sending ' . $confirmation_amount . PHP_EOL;
				
				echo 'Do you want to proceed? Type \'confirm\' to proceed: ';
				
				$line = stream_get_line( STDIN, 10, PHP_EOL );
				
				if( $line != 'confirm' )
				{
				
					echo PHP_EOL;
					
					exit;
				
				}
				
			}
			
		}
		
	}
	
	
	
	
	
	
	// *********************************
	// *** Pre-execution elaboration ***
	// *********************************
	
	
	
	
	
	
	// *** Initialization ***
	
	
	
	if( $command == 'init' )
	{
		$call_return['success'] = 'Init completed';
	}
	
	
	
	// *** Check node connection ***
	
	
	
	elseif( !is_array( $check_node_connection ) || !isset( $check_node_connection['rpc_version'] ) )
	{
		$call_return['error'] = 'Failed node connection';
	}
	
	
	
	// *** Check nano_node path ***
	
	
	
	elseif( !file_exists( $C['nano']['node_file'] ) )
	{
		$call_return['error'] = 'nano_node not found';
	}
	
	
	
	// ***  Check Nano directory ***
	
	
	
	elseif( !file_exists( $C['nano']['data_dir'] ) )
	{
		$call_return['error'] = 'Nano directory not found';
	}
	
	
	
	// *** CLI ***
	
	
	elseif( $flags['cli'] )
	{
		
		$call_return = $nanocli->{ $command }( $arguments );
		
		if( $call_return == null ) $call_return['error'] = 'Bad call';
		
	}
	
	
	
	// *** Call ***
	
	
	
	elseif( $flags['call'] )
	{
		$call_return = $nanocall->{ $command }( $arguments );
	}
	
	
	
	
	
	
	// **********************
	// *** Switch command ***
	// **********************
	
	
	
	
	
	
	else
	{
		
		
	
		switch( $command )
		{
		
			
			
			// *** Print node summary info ***
			
			
			
			case 'status':
			{ 
			
				// Node version
				
				$version = $nanocall->version();
				
				$call_return['node']['version'] = $version['node_vendor'];
				
				// Uptime
				
				$uptime = $nanocall->uptime();
				
				$call_return['node']['uptime'] = $uptime['seconds'];
				
				// Online peers
				
				$peers = $nanocall->peers();
				
				$call_return['network']['peers'] = count( $peers['peers'] );
				
				// Online representatives and weight
				
				$representatives_online = $nanocall->representatives_online( ['weight'=>true] );
				
				$call_return['network']['representatives_online'] = count( $representatives_online['representatives'] );
				
				$weight_cumulative = '0';
				
				foreach( $representatives_online['representatives'] as $representative => $data )
				{			
					$weight_cumulative = gmp_strval( gmp_add( $weight_cumulative, $data['weight'] ) );
				}
				
				$call_return['network']['weight_online'] = $weight_cumulative;
				
				$call_return['network']['weight_online_percent'] = strval( gmp_strval( gmp_div_q( gmp_mul( $weight_cumulative, '10000' ), available_supply ) ) / 100 );
				
				// Blockchain file size
				
				$call_return['node']['blockchain'] = filesize( $C['nano']['data_dir'] . '/data.ldb' );
				
				// Block count
				
				$block_count = $nanocall->block_count();
				
				$call_return['blocks']['count'] = $block_count['count'];
				
				$call_return['blocks']['unchecked'] = $block_count['unchecked'];
				
				$call_return['blocks']['cemented'] = $block_count['cemented'];
				
				$call_return['blocks']['size_average'] = round( filesize( $C['nano']['data_dir'] . '/data.ldb' ) / $block_count["count"] );
				
				// Summary wallets info
				
				$wallets_count = '0';
				
				$wallets_accounts = '0';
				
				$wallets_balance = '0';
				
				$wallets_pending = '0';
				
				$wallets_weight = '0';
				
				$wallet_list = $nanocli->wallet_list();
				
				$wallet_ID = [];
				
				if( is_array( $wallet_list ) && count( $wallet_list ) > 0 )
				{
					
					foreach( $wallet_list as $row )
					{

						$columns = explode( ': ', $row );
						
						if( $columns[0] == 'Wallet ID' )
						{
							$wallet_ID[] = $columns[1];
						}
					
					}
					
					foreach( $wallet_ID as $id )
					{
					
						$wallet_info = $nanocall->wallet_info( ['wallet'=>$id] );
						
						$wallet_weight = $nanocall->wallet_weight( ['wallet'=>$id] );
						
						$wallets_accounts += $wallet_info['accounts_count'];
						
						$wallets_count++;
						
						$wallets_balance = gmp_add( $wallets_balance, $wallet_info['balance'] );
						
						$wallets_pending = gmp_add( $wallets_pending, $wallet_info['pending'] );
					
						$wallets_weight = gmp_add( $wallets_weight, $wallet_weight['weight'] );
					
					}
					
					$wallets_balance = gmp_strval( $wallets_balance );
					
					$wallets_pending = gmp_strval( $wallets_pending );
					
					$wallets_weight = gmp_strval( $wallets_weight );
				
				}
				else
				{
					$call_return['wallets']['error'] = 'No wallets found';
				}	
				
				$call_return['wallets']['balance'] = $wallets_balance;
				
				$call_return['wallets']['pending'] = $wallets_pending;
				
				$call_return['wallets']['weight'] = $wallets_weight;
				
				$call_return['wallets']['count'] = $wallets_count;
				
				$call_return['wallets']['accounts_count'] = $wallets_accounts;
				
				break;
				
			}
			
			
			
			// *** Print wallet list ***
			
			
			
			case 'wallet_list':
			{ 
					
				$wallet_list = $nanocli->wallet_list();
				
				$wallet_ID = [];
				
				if( !is_array( $wallet_list ) || count( $wallet_list ) <= 0 )
				{
					$call_return['error'] = 'No wallets found'; break;
				}
				
				foreach( $wallet_list as $row )
				{

					$columns = explode( ': ', $row );
					
					if( $columns[0] == 'Wallet ID' )
					{
						$wallet_ID[] = $columns[1];
					}
				
				}
				
				foreach( $wallet_ID as $id )
				{
				
					$wallet_info = $nanocall->wallet_info( ['wallet' => $id] );
					
					$wallet_weight = $nanocall->wallet_weight( ['wallet' => $id] );
					
					$wallet_locked = $nanocall->wallet_locked( ['wallet' => $id] );
					
					$call_return[$id]['balance'] = $wallet_info['balance'];
					
					$call_return[$id]['pending'] = $wallet_info['pending'];
					
					$call_return[$id]['weight'] = $wallet_weight['weight'];
					
					$call_return[$id]['accounts_count'] = $wallet_info['accounts_count'];
					
					$call_return[$id]['locked'] = $wallet_locked['locked'];
					
					// $wallet_balances = $nanocall->wallet_balances( ['wallet'=>$id] );
					
					// $call_return[$id]['balances'] = $wallet_balances['balances'];
				
				}
				
				break;
				
			}
			
			
			
			// *** Print wallet info ***
			
			
			
			case 'wallet_info':
			{
				
				if( !isset( $arguments['wallet'] ) )
				{
					$call_return['error'] = 'Bad wallet number'; break;
				}
				
				$wallet_info = $nanocall->wallet_info( ['wallet' => $arguments['wallet']] );
			
				if( isset( $wallet_info['error'] ) )
				{
					$call_return['error'] = 'Bad wallet number'; break;
				}

				$wallet_locked = $nanocall->wallet_locked( ['wallet' => $arguments['wallet']] );
				
				$wallet_weight = $nanocall->wallet_weight( ['wallet'=>$arguments['wallet']] );
				
				$call_return[$arguments['wallet']]['balance'] = $wallet_info['balance'];
				
				$call_return[$arguments['wallet']]['pending'] = $wallet_info['pending'];
				
				$call_return[$arguments['wallet']]['weight'] = $wallet_weight['weight'];
				
				// $call_return[$arguments['wallet']]['weight_percent'] = gmp_strval( gmp_div_q( gmp_mul( $wallet_weight['weight'], '100' ), available_supply ) );
				
				$call_return[$arguments['wallet']]['accounts_count'] = $wallet_info['accounts_count'];
				
				$call_return[$arguments['wallet']]['adhoc_count'] = $wallet_info['adhoc_count'];
				
				$call_return[$arguments['wallet']]['deterministic_count'] = $wallet_info['deterministic_count'];
				
				$call_return[$arguments['wallet']]['deterministic_index'] = $wallet_info['deterministic_index'];
				
				$call_return[$arguments['wallet']]['locked'] = $wallet_locked['locked'];
				
				// $wallet_balances = $nanocall->wallet_balances( ['wallet'=>$arguments['wallet']] );
				
				// $call_return[$arguments['wallet']]['balances'] = $wallet_balances['balances'];
				
				break;
				
			}
			
			
			
			// *** Print wallet weight ***
			
			
			
			case 'wallet_weight':
			{

				if( !isset( $arguments['wallet'] ) )
				{
					$call_return['error'] = 'Bad wallet number'; break;
				}
				
				$wallet_info = $nanocall->wallet_info( ['wallet' => $arguments['wallet']] );
			
				if( isset( $wallet_info['error'] ) )
				{
					$call_return['error'] = 'Bad wallet number'; break;
				}
					
				$wallet_weight = $nanocall->wallet_weight( ['wallet'=>$arguments['wallet'],'sort'=>'desc'] );
				
				$call_return['weight'] = $wallet_weight['weight'];
				
				$call_return['percent'] = gmp_strval( gmp_div_q( gmp_mul( $wallet_weight['weight'], '100' ), available_supply ) );
				
				foreach( $wallet_weight['weights'] as $account => $weight )
				{
				
					$call_return['weights'][$account]['weight'] = $weight;
					
					if( gmp_cmp( $weight, '0' ) > 0 )
					{
						$call_return['weights'][$account]['wallet_percent'] = gmp_strval( gmp_div_q( gmp_mul( $weight, '100' ), $wallet_weight['weight'] ) );
					}
					else
					{
						$call_return['weights'][$account]['wallet_percent'] = '0';
					}
					
				}
				
				break;
				
			}
			
			
			
			// *** Print account info ***
			
			
			
			case 'account_info':
			{
			
				if( !isset( $arguments['account'] ) )
				{
					$call_return['error'] = 'Bad account'; break;
				}
			
				$check_account = $nanocall->validate_account_number( ['account'=>$arguments['account']] );
				
				if( $check_account['valid'] != 1 )
				{
					$call_return['error'] = 'Bad account'; break;
				}
				
				$account_info = $nanocall->account_info( ['account'=>$arguments['account'],'pending'=>true,'weight'=>true,'representative'=>true] );
				
				$account_info['weight_percent'] = gmp_strval( gmp_div_q( gmp_mul( $account_info['weight'], '100' ), available_supply ) );
				
				$call_return[$arguments['account']]['frontier'] = $account_info['frontier'];
				
				$call_return[$arguments['account']]['open_block'] = $account_info['open_block'];
				
				$call_return[$arguments['account']]['representative'] = $account_info['representative'];
				
				$call_return[$arguments['account']]['representative_block'] = $account_info['representative_block'];
				
				$call_return[$arguments['account']]['balance'] = $account_info['balance'];
				
				$call_return[$arguments['account']]['pending'] = $account_info['pending'];
				
				$call_return[$arguments['account']]['weight'] = $account_info['weight'];
				
				$call_return[$arguments['account']]['weight_percent'] = $account_info['weight_percent'];
				
				$call_return[$arguments['account']]['modified_timestamp'] = $account_info['modified_timestamp'];
				
				$call_return[$arguments['account']]['block_count'] = $account_info['block_count'];
				
				$call_return[$arguments['account']]['confirmation_height'] = $account_info['confirmation_height'];
				
				// $call_return[$arguments['account']]['confirmation_height_frontier'] = $account_info['confirmation_height_frontier'];
				
				$call_return[$arguments['account']]['account_version'] = $account_info['account_version'];
				
				break;
			
			}
			
			
			
			// *** Print account delegators ***
			
			
			
			case 'delegators':
			{
			
				if( !isset( $arguments['account'] ) )
				{
					$call_return['error'] = 'Bad account'; break;
				}
				
				$check_account = $nanocall->validate_account_number( ['account'=>$arguments['account']] );
				
				if( $check_account['valid'] != 1 )
				{
					$call_return['error'] = 'Bad account'; break;
				}
					
				// Any balance_min?
				
				$balance_min = isset( $arguments['balance_min'] ) ? $arguments['balance_min'] : '0';
				
				// Any balance_max?
				
				$balance_max = isset( $arguments['balance_max'] ) ? $arguments['balance_max'] : available_supply;
				
				// Any percent_limit?
				
				$percent_limit = isset( $arguments['percent_limit'] ) ? $arguments['percent_limit'] : 100;
				
				// Any limit?
			
				$limit = isset( $arguments['limit'] ) ? (int) $arguments['limit'] : 0;
				
				// Any sort?
			
				$sort = isset( $arguments['sort'] ) ? $arguments['sort'] : 'desc';
				
				//
				
				$delegators_count = $nanocall->delegators_count( ['account'=>$arguments['account']] );
				
				$account_weight = $nanocall->account_weight( ['account'=>$arguments['account']] );
				
				$call_return['weight'] = $account_weight['weight'];
				
				// $call_return['count'] = $delegators_count['count'];

				$delegators = $nanocall->delegators( ['account'=>$arguments['account']] );
				
				if( $sort == 'asc' )
				{
				
					uasort( $delegators['delegators'], function( $a, $b )
					{
						return gmp_cmp( $a, $b );
					});
				
				}
				else
				{
					
					uasort( $delegators['delegators'], function( $a, $b )
					{
						return gmp_cmp( $b, $a );
					});
					
				}
				
				$i = 0;
				
				$balance_cumulative = '0';
				
				$delegators_array = [];
				
				foreach( $delegators['delegators'] as $delegator => $balance )
				{
					
					if( isset( $arguments['balance_min'] ) )
					{
						if( gmp_cmp( $balance, $balance_min ) < 0 ) continue;
					}
					
					if( isset( $arguments['balance_max'] ) )
					{
						if( gmp_cmp( $balance, $balance_max ) > 0 ) continue;
					}
				
					if( $limit <= 0 )
					{}
					else
					{
						if( $i >= $limit ) break;
					}
				
					$i++;
					
					$balance_cumulative = gmp_strval( gmp_add( $balance_cumulative, $balance ) );
				
					$delegators_array[$delegator]['index'] = $i;
				
					$delegators_array[$delegator]['balance'] = $balance;
					
					$delegators_array[$delegator]['balance_cumulative'] = $balance_cumulative;
					
					if( gmp_cmp( $balance, '0' ) > 0 )
					{
						$delegators_array[$delegator]['percent'] = strval( gmp_strval( gmp_div_q( gmp_mul( $balance, '10000' ), $account_weight['weight'] ) ) / 100 );
					}
					else
					{
						$delegators_array[$delegator]['percent'] = '0';
					}
					
					if( gmp_cmp( $balance_cumulative, '0' ) > 0 )
					{
						$delegators_array[$delegator]['percent_cumulative'] = strval( gmp_strval( gmp_div_q( gmp_mul( $balance_cumulative, '10000' ), $account_weight['weight'] ) ) / 100 );
					}
					else
					{
						$delegators_array[$delegator]['percent_cumulative'] = '0';
					}
					
					if( isset( $arguments['percent_limit'] ) )
					{
						if( $delegators_array[$delegator]['percent_cumulative'] >= $percent_limit ) break;
					}
					
				}
				
				$call_return['count'] = $i;
				
				$call_return['delegators'] = $delegators_array;
				
				break;
			
			}
			
			
			
			// *** Print representatives ***
			
			
			
			case 'representatives':
			{
			
				// Any weight_min?
						
				$weight_min = isset( $arguments['weight_min'] ) ? $arguments['weight_min'] : '0';
				
				// Any weight_max?
				
				$weight_max = isset( $arguments['weight_max'] ) ? $arguments['weight_max'] : available_supply;
				
				// Any percent_limit?
				
				$percent_limit = isset( $arguments['percent_limit'] ) ? $arguments['percent_limit'] : 100;
			
				// Any limit?
					
				$limit = isset( $arguments['limit'] ) ? (int) $arguments['limit'] : 0;
				
				//
				
				$representatives = $nanocall->representatives( ['sorting'=>true] );
				
				$i = 0;
				
				$weight_cumulative = '0';
				
				$representatives_array = [];
				
				foreach( $representatives['representatives'] as $representative => $weight )
				{
					
					if( isset( $arguments['weight_min'] ) )
					{
						if( gmp_cmp( $weight, $weight_min ) < 0 ) continue;
					}
					
					if( isset( $arguments['weight_max'] ) )
					{
						if( gmp_cmp( $weight, $weight_max ) > 0 ) continue;
					}
					
					if( $limit <= 0 )
					{}
					else
					{
						if( $i >= $limit ) break;
					}
					
					$i++;
					
					$weight_cumulative = gmp_strval( gmp_add( $weight_cumulative, $weight ) );
					
					$representatives_array[$representative]['index'] = $i;
					
					$representatives_array[$representative]['weight'] = $weight;
					
					$representatives_array[$representative]['weight_cumulative'] = $weight_cumulative;
					
					if( gmp_cmp( $weight, '0' ) > 0 )
					{
						$representatives_array[$representative]['percent'] = strval( gmp_strval( gmp_div_q( gmp_mul( $weight, '10000' ), available_supply ) ) / 100 );
					}
					else
					{
						$representatives_array[$representative]['percent'] = '0';
					}
					
					$representatives_array[$representative]['percent_cumulative'] = strval( gmp_strval( gmp_div_q( gmp_mul( $weight_cumulative, '10000' ), available_supply ) ) / 100 );
					
					if( isset( $arguments['percent_limit'] ) )
					{
						if( $representatives_array[$representative]['percent_cumulative'] >= $percent_limit ) break;
					}
					
				}
				
				// $call_return['count'] = count( $representatives['representatives'] );	
				
				$call_return['weight'] = $weight_cumulative;
				
				$call_return['weight_percent'] = strval( gmp_strval( gmp_div_q( gmp_mul( $weight_cumulative, '10000' ), available_supply ) ) / 100 );
				
				$call_return['count'] = $i;
				
				$call_return['representatives'] = $representatives_array;
				
				break;
			
			}
			
			
			
			// *** Print representatives online ***
			
			
			
			case 'representatives_online':
			{
			
				// Any weight_min?
						
				$weight_min = isset( $arguments['weight_min'] ) ? $arguments['weight_min'] : '0';
				
				// Any weight_max?
				
				$weight_max = isset( $arguments['weight_max'] ) ? $arguments['weight_max'] : available_supply;
				
				// Any percent_limit?
				
				$percent_limit = isset( $arguments['percent_limit'] ) ? $arguments['percent_limit'] : 100;
			
				// Any limit?
					
				$limit = isset( $arguments['limit'] ) ? (int) $arguments['limit'] : 0;
				
				// Any sort?
					
				$sort = isset( $arguments['sort'] ) ? $arguments['sort'] : 'desc';
				
				//
			
				$representatives_online = $nanocall->representatives_online( ['weight'=>true] );
				
				if( $sort == 'asc' )
				{
				
					uasort( $representatives_online['representatives'], function( $a, $b )
					{
						return gmp_cmp( $a['weight'], $b['weight'] );
					});
				
				}
				else
				{
					
					uasort( $representatives_online['representatives'], function( $a, $b )
					{
						return gmp_cmp( $b['weight'], $a['weight'] );
					});
					
				}
				
				$i = 0;
				
				$weight_cumulative = '0';
				
				$representatives_array = [];
				
				foreach( $representatives_online['representatives'] as $representative => $data )
				{
					
					if( isset( $arguments['weight_min'] ) )
					{
						if( gmp_cmp( $data['weight'], $weight_min ) < 0 ) continue;
					}
					
					if( isset( $arguments['weight_max'] ) )
					{
						if( gmp_cmp( $data['weight'], $weight_max ) > 0 ) continue;
					}
					
					if( $limit <= 0 )
					{}
					else
					{
						if( $i >= $limit ) break;
					}
					
					$i++;
					
					$weight_cumulative = gmp_strval( gmp_add( $weight_cumulative, $data['weight'] ) );
					
					$representatives_array[$representative]['index'] = $i;
					
					$representatives_array[$representative]['weight'] = $data['weight'];
					
					$representatives_array[$representative]['weight_cumulative'] = $weight_cumulative;
					
					if( gmp_cmp( $data['weight'], '0' ) > 0 )
					{
						$representatives_array[$representative]['percent'] = strval( gmp_strval( gmp_div_q( gmp_mul( $data['weight'], '10000' ), available_supply ) ) / 100 );
					}
					else
					{
						$representatives_array[$representative]['percent'] = '0';
					}
					
					$representatives_array[$representative]['percent_cumulative'] = strval( gmp_strval( gmp_div_q( gmp_mul( $weight_cumulative, '10000' ), available_supply ) ) / 100 );
					
					if( isset( $arguments['percent_limit'] ) )
					{
						if( $representatives_array[$representative]['percent_cumulative'] >= $percent_limit ) break;
					}
					
				}
				
				// $call_return['weight_cumulative'] = $weight_cumulative;
				
				// $call_return['count'] = count( $representatives_online['representatives'] );
				
				$call_return['weight'] = $weight_cumulative;
				
				$call_return['weight_percent'] = strval( gmp_strval( gmp_div_q( gmp_mul( $weight_cumulative, '10000' ), available_supply ) ) / 100 );
				
				$call_return['count'] = $i;
				
				$call_return['representatives_online'] = $representatives_array;
				
				break;
			
			}
			
			
			
			// *** Print block count ***
			
			
			
			case 'block_count':
			{
			
				// Any sync?
					
				$sync = isset( $arguments['sync'] ) ? (bool) $arguments['sync'] : false;
			
				//
				
				$block_count = $nanocall->block_count();
				
				$call_return = $block_count;
			
				// Blocks sync info
			
				if( $sync )
				{
				
					$sync_blocks_json = file_get_contents( 'https://mynano.ninja/api/blockcount' );
					
					$sync_blocks_array = json_decode( $sync_blocks_json, true );
					
					if( !$sync_blocks_json || !is_array( $sync_blocks_array ) || !isset( $sync_blocks_array['count'] ) )
					{
						$call_return['sync']['error'] = 'Failed API #1'; break;
					}
						
					$call_return['sync']['reference'] = $sync_blocks_array['count'];
					
					$call_return['sync']['difference'] = gmp_strval( gmp_sub( $sync_blocks_array['count'], $block_count['count'] ) );

					$call_return['sync']['percent'] = strval( gmp_strval( gmp_div_q( gmp_mul( $block_count['count'], '10000' ), $sync_blocks_array['count'] ) ) / 100 );
					
				}
				
				break;
			
			}
			
			
			
			// *** Print version ***
			
			
			
			case 'version':
			{
				
				// Any updates?
					
				$updates = isset( $arguments['updates'] ) ? (bool) $arguments['updates'] : false;
			
				//
				
				$version = $nanocall->version();
				
				$call_return = $version;
				
				// Node version sync info
				
				if( $updates )
				{
					
					$options =
					[
						'http' =>
						[
							'method' => "GET",
							'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:74.0) Gecko/20100101 Firefox/74.0\r\n"
						]
					];

					$context = stream_context_create($options);

					$nano_node_json = file_get_contents( 'https://api.github.com/repos/nanocurrency/nano-node/releases/latest', false, $context );
					
					$nano_node_array = json_decode( $nano_node_json, true );
					
					if( !$nano_node_json || !is_array( $nano_node_array ) || !isset( $nano_node_array['tag_name'] ) )
					{
						$call_return['updates']['error'] = 'Failed API #1'; break;
					}
						
					if( version_compare( str_replace( 'Nano V', '', $version['node_vendor'] ), str_replace( 'V', '', $nano_node_array['tag_name'] )  ) >= 0 )
					{
						$call_return['updates']['node_vendor'] = false;
					}
					else
					{
						$call_return['updates']['node_vendor'] = $nano_node_array['tag_name'];
					}

				}
				
				break;
				
			}
			
			
			
			// *** Print ticker vs favourite currencies ***
			
			
			
			case 'ticker':
			{
				
				if( !$C['ticker']['enable'] )
				{
					$call_return['error'] = 'Ticker not enabled'; break;
				}
				
				if( isset( $arguments['amount'] ) )
				{
					$call_return['amount'] = $arguments['amount'];
				}
				else
				{
					$call_return['amount'] = NanoTools::raw2['NANO'];
				}
				
				break;
				
			}
			
			
			
			// *** Update ticker ***
			
			
			
			case 'ticker_update':
			{

				$vs_currency_json = file_get_contents( 'https://api.coingecko.com/api/v3/simple/supported_vs_currencies' );
				
				$vs_currencies_array = json_decode( $vs_currency_json, true );

				if( !$vs_currency_json || !is_array( $vs_currencies_array ) || !isset( $vs_currencies_array[0] ) )
				{
					$call_return['error'] = 'Failed API #1'; break;
				}
					
				// Get latest exchange rates vs currencies
				
				$vs_currencies_string = implode( ',', $vs_currencies_array );
				
				$nano_vs_currency_json = file_get_contents( 'https://api.coingecko.com/api/v3/simple/price?ids=nano&vs_currencies=' . $vs_currencies_string . '&include_last_updated_at=true' );
				
				$nano_vs_currencies_array = json_decode( $nano_vs_currency_json, true );
				
				if( !$nano_vs_currency_json || !is_array( $nano_vs_currencies_array ) || !isset( $nano_vs_currencies_array['nano'] ) )
				{
					$call_return['error'] = 'Failed API #2'; break;
				}

				// All tickers to uppercase
				
				foreach( $nano_vs_currencies_array['nano'] as $currency => $rate )
				{
					
					if( $currency == 'last_updated_at' )
					{
						
						$last_updated_at = $rate;
						
						unset( $nano_vs_currencies_array['nano'][$currency] );
						
						continue;
					
					}
					
					$nano_vs_currencies_array['nano'][strtoupper( $currency )] = $rate;
					
					unset( $nano_vs_currencies_array['nano'][$currency] );
						
				}
				
				$nano_vs_currencies_array['nano']['NANO'] = 1;
				
				$nano_vs_currencies_array['last_updated_at'] = $last_updated_at;
				
				// Save ticker.json
				
				file_put_contents( ticker_file, json_encode( $nano_vs_currencies_array, JSON_PRETTY_PRINT ) );
				
				$C2['ticker_last'] = time();
				
				$call_return['success'] = 'Ticker updated';
				
				break;
			
			}
			
			
			
			// *** Update third-party tags ***
			
			
			
			case 'tags3_update':
			{
				
				$thirdy_party_tags_elaborated['account'] = [];
			
				$third_party_tags_json = file_get_contents( 'https://mynano.ninja/api/accounts/aliases' );
				
				$third_party_tags_array = json_decode( $third_party_tags_json, true );
				
				if( !$third_party_tags_json || !is_array( $third_party_tags_array ) || !isset( $third_party_tags_array[0]['alias'] ) )
				{
					$call_return['error'] = 'Failed API #1'; break;
				}
				
				foreach( $third_party_tags_array as $index => $data )
				{
				
					$tag = $data['alias'];
				
					$tag = tag_filter( $tag );
					
					if( array_key_exists( $tag, $thirdy_party_tags_elaborated['account'] ) ) continue;
				
					if( $tag == '' ) continue;
				
					$thirdy_party_tags_elaborated['account'][$tag] = $data['account'];
				
				}
				
				ksort( $thirdy_party_tags_elaborated['account'] );
				
				// Save tags3.json
				
				file_put_contents( tags3_file, json_encode( $thirdy_party_tags_elaborated, JSON_PRETTY_PRINT ) );
				
				$call_return['success'] = 'tags3 updated';
				
				break;
			
			}
			
			
			
			// *** Print tags ***
			
			
			
			case 'tags':
			{
				
				foreach( $C2['tags']['wallet'] as $tag => $id )
				{
					$call_return['wallet'][] = $id;
				}
				
				foreach( $C2['tags']['account'] as $tag => $id )
				{
					$call_return['account'][] = $id;
				}
				
				foreach( $C2['tags']['block'] as $tag => $id )
				{
					$call_return['block'][] = $id;
				}
				
				break;
				
			}
			
			
			
			// *** Print tags3 ***
			
			
			
			case 'tags3':
			{
				
				if( !$C['tags3']['enable'] )
				{
					$call_return['error'] = 'tags3 not enabled'; break;
				}

				foreach( $C2['tags3']['account'] as $tag => $id )
				{
					$call_return['account'][] = $id;
				}
				
				break;
				
			}
			
			
			
			// *** Add new tag ***
			
			
			
			case 'tag_add':
			{
				
				// Check if cat is defined
				
				if( !isset( $arguments['cat'] ) )
				{
					$call_return['error'] = 'Bad call';	break;
				}
				
				// Check if cat is correct
					
				if( !array_key_exists( $arguments['cat'], $C['tags'] ) )
				{
					$call_return['error'] = 'Bad call';	break;
				}
				
				// Check if tag is defined
				
				if( !isset( $arguments['tag'] ) )
				{
					$call_return['error'] = 'Bad call';	break;
				}
				
				$account_check = explode( '_', $arguments['value'] );
			
				$arguments['tag'] = tag_filter( $arguments['tag'] );
			
				if( $arguments['tag'] == '' )
				{
					$call_return['error'] = 'Bad tag'; break;
				}
				
				// Check if value is defined
				
				if( !isset( $arguments['value'] ) )
				{
					$call_return['error'] = 'Bad call';	break;
				}
				
				// Check account

				if( $arguments['cat'] == 'account' && ( ( $account_check[0] != 'xrb' && $account_check[0] != 'nano' ) || !isset( $account_check[1] ) || strlen( $account_check[1] ) != 60 || !preg_match( "/^[abcdefghijkmnopqrstuwxyz13456789]*$/", $account_check[1] ) ) )
				{
					$call_return['error'] = 'Bad account'; break;
				}
				
				// Check wallet
				
				if( $arguments['cat'] == 'wallet' && ( strlen( $arguments['value'] ) != 64 || !ctype_xdigit( $arguments['value'] ) ) )
				{
					$call_return['error'] = 'Bad wallet number'; break;
				}
				
				// Check block
				
				if( $arguments['cat'] == 'block' && ( strlen( $arguments['value'] ) != 64 || !ctype_xdigit( $arguments['value'] ) ) )
				{
					$call_return['error'] = 'Bad block'; break;
				}
					
				// Check if tag is already used
					
				if( array_key_exists( $arguments['tag'], $C['tags'][$arguments['cat']] ) )
				{
					$call_return['error'] = 'Tag already used'; break;
				}
				
				// Check if value is already used
				
				if( in_array( $arguments['value'], $C['tags']['wallet'] ) || in_array( $arguments['value'], $C['tags']['account'] ) || in_array( $arguments['value'], $C['tags']['block'] ) )
				{
					$call_return['error'] = 'Tag value already used'; break;
				}
				
				//

				$C2['tags'][$arguments['cat']][$arguments['tag']] = $arguments['value'];
				
				$call_return['success'] = 'Tag added';
				
				file_put_contents( tags_file, json_encode( $C2['tags'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
					
				break;
			
			}
			
			
			
			// *** Edit tag ***
			
			
			
			case 'tag_edit':
			{
				
				// Check if cat is defined
				
				if( !isset( $arguments['cat'] ) )
				{
					$call_return['error'] = 'Bad call'; break;
				}
				
				// Check if cat is correct
					
				if( !array_key_exists( $arguments['cat'], $C['tags'] ) )
				{
					$call_return['error'] = 'Bad call'; break;
				}
				
				// Check if tag is defined
				
				if( !isset( $arguments['tag'] ) )
				{
					$call_return['error'] = 'Bad call'; break;
				}
				
				$account_check = explode( '_', $arguments['value'] );
			
				$arguments['tag'] = tag_filter( $arguments['tag'] );
			
				if( $arguments['tag'] == '' )
				{
					$call_return['error'] = 'Bad tag'; break;
				}
				
				// Check if value is defined
				
				if( !isset( $arguments['value'] ) )
				{
					$call_return['error'] = 'Bad call';	break;
				}
				
				// Check account
				
				if( $arguments['cat'] == 'account' && ( ( $account_check[0] != 'xrb' && $account_check[0] != 'nano' ) || !isset( $account_check[1] ) || strlen( $account_check[1] ) != 60 || !preg_match( "/^[abcdefghijkmnopqrstuwxyz13456789]*$/", $account_check[1] ) ) )
				{
					$call_return['error'] = 'Bad account'; break;
				}
				
				// Check wallet
				
				if( $arguments['cat'] == 'wallet' && ( strlen( $arguments['value'] ) != 64 || !ctype_xdigit( $arguments['value'] ) ) )
				{
					$call_return['error'] = 'Bad wallet number'; break;
				}
				
				// Check block
				
				if( $arguments['cat'] == 'block' && ( strlen( $arguments['value'] ) != 64 || !ctype_xdigit( $arguments['value'] ) ) )
				{
					$call_return['error'] = 'Bad block'; break;
				}
				
				// Check if tag is already used
				
				if( array_key_exists( $arguments['tag'], $C['tags'][$arguments['cat']] ) )
				{
					$call_return['error'] = 'Tag already used'; break;
				}
				
				// Check if value is already used
				
				if( in_array( $arguments['value'], $C['tags']['wallet'] ) || in_array( $arguments['value'], $C['tags']['account'] ) || in_array( $arguments['value'], $C['tags']['block'] ) )
				{
					$call_return['error'] = 'Tag value already used'; break;
				}
				
				//
				
				$C2['tags'][$arguments['cat']][$arguments['tag']] = $arguments['value'];
				
				$call_return['success'] = 'Tag edited';
				
				file_put_contents( tags_file, json_encode( $C2['tags'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
				
				break;
			
			}
			
			
			
			// *** Remove tag ***
			
			
			
			case 'tag_remove':
			{
				
				// Check if cat is defined
				
				if( !isset( $arguments['cat'] ) )
				{
					$call_return['error'] = 'Bad call';	break;
				}
				
				// Check if cat is correct
					
				if( !array_key_exists( $arguments['cat'], $C['tags'] ) )
				{
					$call_return['error'] = 'Bad call';	break;
				}
				
				// Check if tag is defined
				
				if( !isset( $arguments['tag'] ) )
				{
					$call_return['error'] = 'Bad call';	break;
				}
					
				$arguments['tag'] = tag_filter( $arguments['tag'] );
			
				if( $arguments['tag'] == '' )
				{
					$call_return['error'] = 'Bad tag'; break;
				}
				
				// Check if tag exists
			
				if( !array_key_exists( $arguments['tag'], $C['tags'][$arguments['cat']] ) )
				{
					$call_return['error'] = 'Tag not found'; break;
				}
				
				//
				
				unset( $C2['tags'][$arguments['cat']][$arguments['tag']] );
				
				$call_return['success'] = 'Tag removed';
				
				file_put_contents( tags_file, json_encode( $C2['tags'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
					
				break;
			
			}
			
			
			
			// *** Default node call ***
			
			
			
			default:
			{
				$call_return = $nanocall->{ $command }( $arguments ); break;
			}
		
		
		
		}
	
	
	
	}
	
	
	
	
	
	
	// **********************************
	// *** Post-execution elaboration ***
	// **********************************




	

	// *** Check if ticker is updated ***
	
	
	
	if( $C['ticker']['enable'] )
	{
	
		$ticker_delay = time() - $C2['ticker_last'];
	
		if( $ticker_delay > 60*30 )
		{
			$alerts[] = 'Ticker not updated';
		}
	
	}
	
	
	
	
	
	
	// ********************
	// *** Build output ***
	// ********************
	
	
	
	
	
	
	if( !$flags['raw_out'] )
	{
		$call_return = eleborate_output( $call_return );
	}
	
	if( count( $alerts ) > 0 ) $call_return['alert'] = $alerts;
	
	if( $flags['json_out'] )
	{
		
		echo json_encode( $call_return );
		
		echo "\n";
		
	}
	else
	{
		
		echo PHP_EOL;
			
		echo pretty_print_r( $call_return );

		echo PHP_EOL;
	
	}
	
	
	
	
	
	
	// ************
	// *** Logs ***
	// ************
	
	
	
	
	
	
	// *** Clean logs? ***
	
	
	
	if( $C['log']['expiration'] > 0 )
	{
		
		$logs = array_diff( scandir( log_dir ), array( '.', '..' ) );
		
		foreach( $logs as $log )
		{
		
			$log = explode( '.', $log );
			
			if( isset( $log[1] ) && $log[1] == 'txt' )
			{
				
				if( strtotime( $log[0] ) < ( time() - ( ( $C['log']['expiration'] + 1 ) * 24 * 60 * 60 ) ) )
				{
					unlink( log_dir . '/' . $log[0] . '.' . $log[1] );
				}
				
			}
		
		}
		
	}
	
	
	
	// *** Save log? ***
	
	
	
	if( !$flags['no_log'] )
	{
	
		$check_words = 
		[
			'deterministic_key',
			'key_create',
			'key_expand',
			'node_id',
			'password_change',
			'password_enter',
			'vanity_account',
			'wallet_add',
			'wallet_change_seed',
			'wallet_create',
			'wallet_export'
		];
		
		if( $C['log']['save'] && ( !in_array( $command, $check_words ) || !$C['log']['privacy'] ) )
		{
			
			// Generate flags string
		
			$log_flags = [];
			
			foreach( $flags as $name => $value )
			{
				
				if( $value )
				{
					$log_flags[] = $name;
				}
			
			}
			
			$log_flags = implode( ',', $log_flags );
			
			if( $log_flags == '' ) $log_flags = 'noflags';
		
			// Save log
		
			$log_file = log_dir . '/' . date( 'Y-m-d' ) . '.txt';
		
			if( !file_exists( $log_file ) )
			{
				$newline = null;
			}
			else
			{
				$newline = PHP_EOL;
			}
			
			file_put_contents( $log_file, $newline . date( 'm/d/Y H:i:s', time() ) . ' ' . $callerID . ' ' . $command . ' ' . json_encode( $arguments ) . ' ' . $log_flags . ' ' . json_encode( $call_return ), FILE_APPEND );
		
		}
	
	}
		
?>