<?php

	/*

	CONFIGURATION:
	
		- Set the configuration file:
		
			php PATH/php4nano/ncm/ncm.php init
			
		- Edit data/config.json with you own parameters
		
		- Specifications for config.json:
			
			- Denomination
			
				Accepted denominations: unano, mnano, nano, knano, Mnano, NANO, Gnano
			
			- Log privacy
			
				Disable logging for sensitive information
							
			- Ticker
			
				********************************************************************************
				*** A big THANK YOU to http://coingecko.com for its free and accessible API! ***
				********************************************************************************
			
				Before enabling the ticker option, crontab 'php PATH/php4nano/ncm/ncm.php ticker_update' (I suggest execution every 20 minutes)
				Also, initialize it by executing it manually the first time
			
			- Tags
			
				Do not leave empty tags!
				Only one tag for each wallet/account/block ID
				In order to have a clean and flowing tag list, I recommend using only alphanumeric characters, dashes(-) and dottes(.)
				
				Note: tags set by you will always take precedence over those of third party
				
			- Third Party Tags (3tags)
			
				********************************************************************************
				*** A big THANK YOU to https://mynano.ninja for its free and accessible API! ***
				********************************************************************************
				
				Enable 3tags option and run 'php PATH/php4nano/ncm/ncm.php 3tags_update' to populate third party tags
				You may crontab it to keep it updated
				
	USAGE:
	
		Default input/output amount denomination in NANO (Mnano)
		
		Input array elements comma separated (you can also use tags)
		
		- Create a shortcut for ncm.php adding to .bashrc:
			
			alias ncm='php PATH/php4nano/ncm/ncm.php'
		
		- Command examples:
		
			// ncm dedicated
		
			ncm init                                                                           		init   configuration file
			ncm status                                                                         		print  node summary
			ncm account_info account=<accountID|tag>												print  account info (override regular call)
			ncm wallet_list                                                                    		print  all wallets summary
			ncm wallet_info wallet=<walletID|tag>	                                                print  wallet summary (override regular call)
			ncm wallet_weight wallet=<walletID|tag>													print  wallet weight (override regular call)
			ncm delegators account=<accountID|tag> count=<limit> atleast=<balance>					print  delegators summary (override regular call)
			ncm representatives count=<limit> atleast=<weight>										print  representatives and their weight (override regular call)
			ncm representatives_online count=<limit> atleast=<weight>								print  online representatives (override regular call)
			ncm ticker                                                                         		print  latest NANO price compared to favourite vs currencies (if ticker enabled)
			ncm ticker amount=1
			ncm ticker amount=1-USD
			ncm ticker_update                                                                		update ticker.json
			ncm 3tags_update                                                                 		update 3tags.json
			ncm config                                                                        		print  config.json (no tags)
			ncm tags                                                                          		print  tags
			ncm 3tags																		  		print  3tags
			ncm tag_add cat=<account|block|wallet> tag=<tag> value=<accountID|blockID|walletID>     add    tag
			ncm tag_edit cat=<account|block|wallet> tag=<tag> value=<accountID|blockID|walletID>    edit   tag
			ncm tag_remove cat=<account|block|wallet> tag=<tag>                                   	remove tag
			
			// Node call
			
			ncm block_count
			ncm wallet_balances wallet=<walletID|tag>
			ncm send wallet=<walletID|tag> source=<accountID|tag> destination=<accountID|tag> amount=1 id=uniqid (uniqid value will create a php random unique id)
			ncm send wallet=<walletID|tag> source=<accountID|tag> destination=<accountID|tag> amount=1-USD id=uniqid (if ticker enabled)
			ncm accounts_balances accounts=tag1,nano_1nanode8ngaakzbck8smq6ru9bethqwyehomf79sae1k7xd47dkidjqzffeg,tag2 (example of array parameter)
			
			Read full RPC documentation at https://docs.nano.org/commands/rpc-protocol/
			
			// Node call extension
			
			ncm wallet_wipe wallet=<walletID|tag> destination=<accountID|tag> order=<asc|desc>
			ncm wallet_send wallet=<walletID|tag> destination=<accountID|tag> amount=1 order=<asc|desc>
			ncm wallet_send wallet=<walletID|tag> destination=<accountID|tag> amount=1-USD order=<asc|desc> (if ticker enabled)
			ncm wallet_weight wallet=<walletID|tag> order=<asc|desc>
			
	TIPS:
			
		- Using raw_in=true as argument will skip any input elaboration (faster execution, machine-like input)
			
			- to be effective it must be the first argument, example: ncm wallet_balance raw_in=true wallet=<walletID>
			- input tags not available, only Nano-raw input amounts, simplified input array not available
				
		- Using raw_out=true as argument will output a raw encoded json (faster execution, machine-like output)
			
			- output tags not available, only Nano-raw output amounts
			
		- Using no_log=true as argument won't save log regardless of what you set up in config.json
	
	*/
	
	
	
	
	
	
	// *****************
	// *** Libraries ***
	// *****************
	
	
	
	
	
	
	require_once __DIR__ . '/../lib/NanoTools.php';
	
	require_once __DIR__ . '/../lib/NanoCLI.php';
	
	require_once __DIR__ . '/../lib/NanoRPC.php';
	
	require_once __DIR__ . '/../lib/NanoRPCExtension.php';
	
	
	
	
	
	
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
	


	// *** Notable string ***
	
	
	
	function notable_string( string $string )
	{
		return PHP_EOL . '*** ' . $string . ' ***' . PHP_EOL;
	}
	
	
	
	// *** Custom number format ***
	
	
	
	function custom_number( string $number, int $decimals = -1 )
	{
		
		global $C;
		
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
	
	
	
	// *** Tag replace ***
	
	
	
	function tag_replace( $value )
	{
		
		global $C;
		
		if( !$C['tag']['view'] ) return $value;
		
		if( is_array( $value ) ) return $value;
		
		$key_check = explode( '_', $value );
	
		if( array_search( $value, $C['tags']['wallet'] ) ) // Find a wallet tag
		{
			return array_search( $value, $C['tags']['wallet'] ) . $C['tag']['separator'] . $value;
		}
		elseif( isset( $key_check[1] ) && ( $key_check[0] == 'xrb' || $key_check[0] == 'nano' ) ) // Find an account tag
		{

			if( array_search( 'xrb_' . $key_check[1], $C['tags']['account'] ) )
			{
				return array_search( 'xrb_' . $key_check[1], $C['tags']['account'] ) . $C['tag']['separator'] . $value;
			}
			elseif( array_search( 'nano_' . $key_check[1], $C['tags']['account'] ) )
			{
				return array_search( 'nano_' . $key_check[1], $C['tags']['account'] ) . $C['tag']['separator'] . $value;
			}
			elseif( $C['3tags']['enable'] && array_search( 'xrb_' . $key_check[1], thirdtags['account'] ) )
			{
				return array_search( 'xrb_' . $key_check[1], thirdtags['account'] ) . $C['tag']['separator'] . $value;
			}
			elseif( $C['3tags']['enable'] && array_search( 'nano_' . $key_check[1], thirdtags['account'] ) )
			{
				return array_search( 'nano_' . $key_check[1], thirdtags['account'] ) . $C['tag']['separator'] . $value;
			}
			else
			{
				return $value;
			}
		
		}
		elseif( array_search( $value, $C['tags']['block'] ) ) // Find a block tag
		{
			return array_search( $value, $C['tags']['block'] ) . $C['tag']['separator'] . $value;
		}
		else
		{
			return $value;
		}
		
	}
	
	
	
	// *** Pretty array ***
	
	
	
	function pretty_array( array $array )
	{
		
		global $C;
		
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
				
				$key = tag_replace( $key );
				
				$array[$key] = pretty_array( $value );
				
			}
			
			// It is not an array but it's a encoded json
			
			elseif( !is_array( $value ) && $key == 'contents' )
			{
			
				$array[$key] = pretty_array( json_decode( $value, true ) );
			
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
						'online_stake_total',
						'online_weight_minimum',
						'peers_stake_required',
						'peers_stake_total',
						'pending',
						'quorum_delta',
						'receive_minimum',
						'vote_minimum',
						'weight'
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
							
								if( isset( vs_currencies[strtoupper( $fav_vs_currency )] ) )
								{
									$array[$key][] = custom_number( number_format( NanoTools::raw2den( $value, 'NANO' ) * vs_currencies[strtoupper( $fav_vs_currency )], 8, '.', '' ) ) . ' ' . strtoupper( $fav_vs_currency );
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
						'data.ldb'
					];
					
					if( in_array( $key, $check_words ) && is_numeric( $value ) )
					{
						$array[$key] = custom_number( $value/1000000, 0 ) . ' MB';
					}
					
					$check_words = 
					[
						'average_size'
					];
				
					if( in_array( $key, $check_words ) && is_numeric( $value ) )
					{
						$array[$key] = custom_number( $value, 0 ) . ' B';
					}
					
				// Error format
				
					if( $key == 'error' && $value == 'Unable to parse JSON' ) $array[$key] = bad_call;
					
					if( $key == 'error' && $value == 'Unable to parse Array' ) $array[$key] = bad_call;
				
				// Tag replacement
				
					$array[$key] = tag_replace( $array[$key] );
				
			}
			
		}
		
		return $array;
		
	}
	

	
	

	
	// *********************
	// *** Configuration ***
	// *********************
	
	
	
	
	
	
	define( 'data_dir'   		, __DIR__ . '/data' );
	
	define( 'log_dir'    		, __DIR__ . '/log' );
	
	define( 'config_file'		, data_dir . '/config.json' );
	 
	define(	'ticker_file'	 	, data_dir . '/ticker.json' );
	
	define( 'thirdtags_file' 	, data_dir . '/3tags.json' );
	
	define( 'tabulation' 		, '    ' );
	
	define( 'bad_call'   	 	, 'Bad call' );

	define( 'no_connection'  	, 'No node connection' );
	
	define( 'bad_wallet'     	, 'Bad wallet number' );
	
	define( 'bad_account'   	, 'Bad account' );
	
	define( 'available_supply'	, '133248061996216572282917317807824970865');
	
	
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

	
	
	// *** Config model ***
	
	
	
	$C_model_raw =
	'{
		"nano": {
			"denomination": "NANO",
			"node_file": "/home/nano/nano_node",
			"data_dir": "/home/nano/Nano",
			"connection": "rpc",
			"rpc": {
				"host": "localhost",
				"port": "7076"
			}
		},
		"log": {
			"save": true,
			"privacy": true
		},
		"timezone": "UTC",
		"format": {
			"timestamp": "m/d/Y H:i:s",
			"decimal": ".",
			"thousand": ","
		},
		"ticker": {
			"enable": false,
			"fav_vs_currencies": "BTC,USD"
		},
		"tag": {
			"view" : true,
			"separator": "|"
		},
		"tags": {
			"account": {
				"genesis": "nano_3t6k35gi95xu6tergt6p69ck76ogmitsa8mnijtpxm9fkcm736xtoncuohr3"
			},
			"block": {
				"genesis": "991CF190094C00F0B68E2E5F75F6BEE95A2E0BD93CEAA4A6734DB9F19B728948"
			},
			"wallet": {}
		},
		"3tags": {
			"enable": false
		}
	}';
	
	$C_model = json_decode( $C_model_raw, true );
	
	
	
	// *** Load config.json ***
	
	
	
	// If config.json is not found, initialize a model like one
	
	if( !file_exists( config_file ) )
	{
		$C = $C_model;
	}
	
	// Else load config.json
	
	else
	{
		
		$C = json_decode( file_get_contents( config_file ), true );
	
		// Insert standard configuration if missing elements
		
		$C = array_merge_new_recursive( $C, $C_model );
		
	}
	
	// Complete configuration
	
	date_default_timezone_set( $C['timezone'] );
	
	if( $C['ticker']['enable'] )
	{
		
		$ticker_array = json_decode( file_get_contents( ticker_file ), true );
		
		define( 'vs_currencies' , $ticker_array['nano'] );
		
		define( 'ticker_last', $ticker_array['last_updated_at'] );
		
	}
	
	if( $C['3tags']['enable'] )
	{
		define( 'thirdtags', json_decode( file_get_contents( thirdtags_file ), true ) );
	}
	
	// Save config.json
	
	file_put_contents( config_file, json_encode( $C, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
	
	
	
	
	
	
	// *****************
	// *** Get input ***
	// *****************
	
	
	
	
	
	
	if( count( $argv ) < 2 ) exit;
	
	$command = $argv[1];
	
	unset( $argv[0] );
	
	unset( $argv[1] );
	
	$argv = array_values( $argv );
	
	
	
	
	
	
	// ************************
	// *** Node connections ***
	// ************************
	
	
	
	
	
	
	// Node CLI
	
	$nanocli = new NanoCLI( $C['nano']['node_file'] );
	
	// Node call
	
	if( $C['nano']['connection'] == 'rpc' )
	{
		$nanoconn = new NanoRPCExtension( $C['nano']['rpc']['host'], $C['nano']['rpc']['port'] );
		
	}
	
	
	
	
	
	
	// *******************
	// *** Build input ***
	// *******************
	
	
	
	
	
	
	$arguments = [];
	
	$raw_in = false;
	
	$raw_out = false;
	
	$no_log = false;
	
	$alerts = [];
	
	foreach( $argv as $arg )
	{
		
		$arguments_row = [];
		
		$arguments_row = explode( '=', $arg, 2 );
		
		if( !isset( $arguments_row[1] ) )
		{
			$arguments_row[1] = '';
		}
		
		// Skip input elaboration?
		
		if( $arguments_row[0] == 'raw_in' && $arguments_row[1] )
		{
			
			$raw_in = true;
			
			continue;
		
		}
		
		// Output as json or pretty?
		
		if( $arguments_row[0] == 'raw_out' && $arguments_row[1] )
		{
			
			$raw_out = true;
			
			continue;
		
		}
		
		// Ignore log?
		
		if( $arguments_row[0] == 'no_log' && $arguments_row[1] )
		{
			
			$no_log = true;
			
			continue;
		
		}
		
		
		
		// *** Raw input ***
		
		
		
		if( $raw_in )
		{
			
			$arguments[$arguments_row[0]] = $arguments_row[1];
			
			continue;
		
		}
		
		
		
		// *** Elaborated input ***
		
		
		
		// Elaborate accounts array
		
		$check_words = ['accounts'];
		
		if( in_array( $arguments_row[0], $check_words ) )
		{
		
			$arguments_row_array = [];
			
			$arguments_row_raw = explode( ',', $arguments_row[1] );
			
			// Check if an account tag is available
			
			foreach( $arguments_row_raw as $argument_raw )
			{
		
				if( array_key_exists( $argument_raw, $C['tags']['account'] ) )
				{
					$argument_raw = $C['tags']['account'][$argument_raw];
				}
				elseif( $C['3tags']['enable'] && array_key_exists( $argument_raw, thirdtags['account'] ) )
				{
					$argument_raw = thirdtags['account'][$argument_raw];
				}
				else
				{}
				
				$arguments_row_array[] = $argument_raw;
			
			}
			
			$arguments_row[1] = $arguments_row_array;
			
		}
		
		// Elaborate blocks array
		
		$check_words = ['hashes'];
		
		if( in_array( $arguments_row[0], $check_words ) )
		{
		
			$arguments_row_array = [];
			
			$arguments_row_raw = explode( ',', $arguments_row[1] );
			
			// Check if a block tag is available
			
			foreach( $arguments_row_raw as $argument_raw )
			{
		
				if( array_key_exists( $argument_raw, $C['tags']['block'] ) )
				{
					$argument_raw = $C['tags']['block'][$argument_raw];
				}
				
				$arguments_row_array[] = $argument_raw;
			
			}
			
			$arguments_row[1] = $arguments_row_array;
			
		}
		
		// Check if a wallet tag is available
		
		$check_words = ['wallet'];
		
		if( in_array($arguments_row[0], $check_words ) )
		{
			
			if( array_key_exists( $arguments_row[1], $C['tags']['wallet'] ) )
			{
				$arguments_row[1] = $C['tags']['wallet'][$arguments_row[1]];
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
		
		if( in_array( $arguments_row[0], $check_words ) )
		{
			
			if( array_key_exists( $arguments_row[1], $C['tags']['account'] ) )
			{
				$arguments_row[1] = $C['tags']['account'][$arguments_row[1]];
			}
			elseif( $C['3tags']['enable'] && array_key_exists( $arguments_row[1], thirdtags['account'] ) )
			{
				$arguments_row[1] = thirdtags['account'][$arguments_row[1]];
			}
			else
			{}
			
		}
		
		// Check if an block tag is available
		
		$check_words = ['hash'];
		
		if( in_array( $arguments_row[0], $check_words ) )
		{
			
			if( array_key_exists( $arguments_row[1], $C['tags']['block'] ) )
			{
				$arguments_row[1] = $C['tags']['block'][$arguments_row[1]];
			}
			
		}
		
		// Convert denomination to raw
		
		$check_words = ['amount','atleast'];
		
		if( in_array( $arguments_row[0], $check_words ) )
		{
			
			if( $C['ticker']['enable'] && !is_numeric( $arguments_row[1] ) ) // Input as other currency?
			{
			
				$input_currency = explode( '-', $arguments_row[1] );
				
				$input_currency[0] = abs( $input_currency[0] );
				
				if( is_numeric( $input_currency[0] ) && isset( $input_currency[1] ) && isset( vs_currencies[strtoupper( $input_currency[1] )] ) )
				{
					$arguments_row[1] = NanoTools::den2raw( $input_currency[0] / vs_currencies[strtoupper( $input_currency[1] )], 'NANO' );
				}
				else
				{
					$arguments_row[1] = 0;
				}
				
			}
			else // Input as a Nano denomination?
			{
				
				if( is_numeric( $arguments_row[1] ) && abs( $arguments_row[1] ) == $arguments_row[1] )
				{
					$arguments_row[1] = NanoTools::den2raw( $arguments_row[1], $C['nano']['denomination'] );
				}
				else
				{
					$arguments_row[1] = 0;
				}
				
			}
			
		}
		
		// Generate automatic unique id for send command
		
		if( $command == 'send' && $arguments_row[0] == 'id' && $arguments_row[1] == 'uniqid' )
		{
			$arguments_row[1] = uniqid();
		}
		
		$arguments[$arguments_row[0]] = $arguments_row[1];
	
	}

	
	
	
	
	
	// **************************************
	// *** Confirmation if sending amount ***
	// **************************************
	
	
	
	
	
	
	if( !$raw_in )
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
					
					$wallet_info = $nanoconn->wallet_info( ['wallet'=>$arguments['wallet']] );
					
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
					
					$wallet_info = $nanoconn->wallet_info( ['wallet'=>$arguments['wallet']] );
					
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
				
				echo notable_string( "Sending $confirmation_amount" ) . PHP_EOL;
				
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
	
	
	
	
	
	
	// **********************
	// *** Switch command ***
	// **********************
	
	
	
	
	
	
	$call_return = [];
	
	
	
	// *** Print node and summary info ***
	
	
	
	if( $command == 'status' )
	{ 
	
		// Node version
		
		$version = $nanoconn->version();
		
		$call_return['version'] = $version['node_vendor'];
		
		// Uptime
		
		$uptime = $nanoconn->uptime();
		
		$call_return['uptime'] = $uptime['seconds'];
		
		// Online peers
		
		$peers = $nanoconn->peers();
		
		$call_return['peers'] = count( $peers['peers'] );
		
		// Blockchain file size
		
		$call_return['data.ldb'] = filesize( $C['nano']['data_dir'] . '/data.ldb' );
		
		// Block count
		
		$block_count = $nanoconn->block_count();
		
		$call_return['blocks']['count'] = $block_count["count"];
		
		$call_return['blocks']['unchecked'] = $block_count["unchecked"];
		
		$call_return['blocks']['cemented'] = $block_count["cemented"];
		
		// Bytes per block
		
		$call_return['blocks']['average_size'] = round( filesize( $C['nano']['data_dir'] . '/data.ldb' ) / $block_count["count"] );
		
		// Summary wallets info
		
		$wallets_count = '0';
		
		$wallets_accounts = '0';
		
		$wallets_balance = '0';
		
		$wallets_pending = '0';
		
		$wallets_weight = '0';
		
		foreach( $C['tags']['wallet'] as $tag => $id )
		{
		
			$wallet_info = $nanoconn->wallet_info( ['wallet'=>$id] );
			
			$wallet_weight = $nanoconn->wallet_weight( ['wallet'=>$id] );
			
			$wallets_accounts += $wallet_info['accounts_count'];
			
			$wallets_count++;
			
			$wallets_balance = gmp_add( $wallets_balance, $wallet_info['balance'] );
			
			$wallets_pending = gmp_add( $wallets_pending, $wallet_info['pending'] );
		
			$wallets_weight = gmp_add( $wallets_weight, $wallet_weight['weight'] );
		
		}
		
		$wallets_balance = gmp_strval( $wallets_balance );
		
		$wallets_pending = gmp_strval( $wallets_pending );
		
		$wallets_weight = gmp_strval( $wallets_weight );
		
		$wallet_weight = $nanoconn->wallet_weight( ['wallet'=>$id] );
		
		$call_return['wallets']['balance'] = $wallets_balance;
		
		$call_return['wallets']['pending'] = $wallets_pending;
		
		$call_return['wallets']['weight'] = $wallets_weight;
		
		$call_return['wallets']['count'] = $wallets_count;
		
		$call_return['wallets']['accounts_count'] = $wallets_accounts;
		
		if( !is_null( $nanoconn->error ) )
		{
			$call_return['error'] = no_connection;
		}
		
	}
	
	
	
	// *** Print wallet list ***
	
	
	
	elseif( $command == 'wallet_list' )
	{ 
			
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
			
				$wallet_info = $nanoconn->wallet_info( ['wallet' => $id] );
				
				$wallet_weight = $nanoconn->wallet_weight( ['wallet' => $id] );
				
				$wallet_locked = $nanoconn->wallet_locked( ['wallet' => $id] );
				
				$call_return[$id]['balance'] = $wallet_info['balance'];
				
				$call_return[$id]['pending'] = $wallet_info['pending'];
				
				$call_return[$id]['weight'] = $wallet_weight['weight'];
				
				$call_return[$id]['accounts_count'] = $wallet_info['accounts_count'];
				
				$call_return[$id]['locked'] = $wallet_locked['locked'];
				
				// $wallet_balances = $nanoconn->wallet_balances( ['wallet'=>$id] );
				
				// $call_return[$id]['balances'] = $wallet_balances['balances'];
			
			}
		
		}
		else
		{
			$call_return['error'] = 'No wallets found';
		}
		
		if( !is_null( $nanoconn->error ) )
		{
			$call_return['error'] = no_connection;
		}
		
	}
	
	
	
	// *** Prints wallet info ***
	
	
	
	elseif( $command == 'wallet_info' )
	{
		
		if( isset( $arguments['wallet'] ) )
		{
		
			$wallet_info = $nanoconn->wallet_info( ['wallet' => $arguments['wallet']] );
		
			if( isset( $wallet_info['error'] ) )
			{
				$call_return['error'] = bad_wallet;
			}
			else
			{
				
				$wallet_locked = $nanoconn->wallet_locked( ['wallet' => $arguments['wallet']] );
				
				$wallet_weight = $nanoconn->wallet_weight( ['wallet'=>$arguments['wallet']] );
				
				$call_return[$arguments['wallet']]['balance'] = $wallet_info['balance'];
				
				$call_return[$arguments['wallet']]['pending'] = $wallet_info['pending'];
				
				$call_return[$arguments['wallet']]['weight'] = $wallet_weight['weight'];
				
				// $call_return[$arguments['wallet']]['weight_percent'] = gmp_strval( gmp_div_q( gmp_mul( $wallet_weight['weight'], '100' ), available_supply ) );
				
				$call_return[$arguments['wallet']]['accounts_count'] = $wallet_info['accounts_count'];
				
				$call_return[$arguments['wallet']]['adhoc_count'] = $wallet_info['adhoc_count'];
				
				$call_return[$arguments['wallet']]['deterministic_count'] = $wallet_info['deterministic_count'];
				
				$call_return[$arguments['wallet']]['deterministic_index'] = $wallet_info['deterministic_index'];
				
				$call_return[$arguments['wallet']]['locked'] = $wallet_locked['locked'];
				
				// $wallet_balances = $nanoconn->wallet_balances( ['wallet'=>$arguments['wallet']] );
				
				// $call_return[$arguments['wallet']]['balances'] = $wallet_balances['balances'];
				
			}
		
		}
		else
		{
			$call_return['error'] = bad_wallet;
		}
		
		if( !is_null( $nanoconn->error ) )
		{
			$call_return['error'] = no_connection;
		}
		
	}
	
	
	
	// *** Wallet weight ***
	
	
	
	elseif( $command == 'wallet_weight' )
	{

		if( isset( $arguments['wallet'] ) )
		{
		
			$wallet_info = $nanoconn->wallet_info( ['wallet' => $arguments['wallet']] );
		
			if( isset( $wallet_info['error'] ) )
			{
				$call_return['error'] = bad_wallet;
			}
			else
			{
				
				$wallet_weight = $nanoconn->wallet_weight( ['wallet'=>$arguments['wallet'],'order'=>'desc'] );
				
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
				
			}
			
		}
		else
		{
			$call_return['error'] = bad_wallet;
		}
		
		if( !is_null( $nanoconn->error ) )
		{
			$call_return['error'] = no_connection;
		}
		
	}
	
	
	
	// *** Account info ***
	
	
	
	elseif( $command == 'account_info' )
	{
	
		if( isset( $arguments['account'] ) )
		{
		
			$check_account = $nanoconn->validate_account_number( ['account'=>$arguments['account']] );
			
			if( $check_account['valid'] != 1 )
			{
				$call_return['error'] = bad_account;
			}
			else
			{
			
				$account_info = $nanoconn->account_info( ['account'=>$arguments['account'],'pending'=>true,'weight'=>true,'representative'=>true] );
				
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
			
			}
			
		}
		else
		{
			$call_return['error'] = bad_account;
		}
		
		if( !is_null( $nanoconn->error ) )
		{
			$call_return['error'] = no_connection;
		}
	
	}
	
	
	
	// *** Delegators ***
	
	
	
	elseif( $command == 'delegators' )
	{
	
		if( isset( $arguments['account'] ) )
		{
		
			$check_account = $nanoconn->validate_account_number( ['account'=>$arguments['account']] );
			
			if( $check_account['valid'] != 1 )
			{
				$call_return['error'] = bad_account;
			}
			else
			{
				
				// Any atleast?
				
				$atleast = isset( $arguments['atleast'] ) ? $arguments['atleast'] : '0';
				
				// Any limit?
			
				$limit = isset( $arguments['count'] ) ? (int) $arguments['count'] : 0;
			
				$delegators_count = $nanoconn->delegators_count( ['account'=>$arguments['account']] );
				
				$account_weight = $nanoconn->account_weight( ['account'=>$arguments['account']] );
				
				$call_return['weight'] = $account_weight['weight'];
				
				$call_return['count'] = $delegators_count['count'];
			
				$delegators = $nanoconn->delegators( ['account'=>$arguments['account']] );
				
				uasort( $delegators['delegators'], function( $a, $b )
				{
					
					return gmp_cmp( $b, $a );
					
				});
				
				$i = 0;
				
				foreach( $delegators['delegators'] as $delegator => $balance )
				{
					
					if( gmp_cmp( $balance, $atleast ) < 0 ) continue;
				
					if( $limit <= 0 )
					{}
					else
					{
					
						if( $i >= $limit ) break;
					
						$i++;
					
					}
				
					$call_return['delegators'][$delegator]['balance'] = $balance;
					
					if( gmp_cmp( $balance, '0' ) > 0 )
					{
						$call_return['delegators'][$delegator]['percent'] = gmp_strval( gmp_div_q( gmp_mul( $balance, '100' ), $account_weight['weight'] ) );
					}
					else
					{
						$call_return['delegators'][$delegator]['percent'] = '0';
					}
					
				}
			
			}
			
		}
		else
		{
			$call_return['error'] = bad_account;
		}
		
		if( !is_null( $nanoconn->error ) )
		{
			$call_return['error'] = no_connection;
		}
	
	}
	
	
	
	// *** Representatives ***
	
	
	
	elseif( $command == 'representatives' )
	{
	
		// Any atleast?
				
		$atleast = isset( $arguments['atleast'] ) ? $arguments['atleast'] : '0';
	
		// Any limit?
			
		$limit = isset( $arguments['count'] ) ? (int) $arguments['count'] : 0;
	
		$representatives = $nanoconn->representatives( ['sorting'=>true] );
		
		$i = 0;
		
		foreach( $representatives['representatives'] as $representative => $weight )
		{
			
			if( gmp_cmp( $weight, $atleast ) < 0 ) continue;
			
			if( $limit <= 0 )
			{}
			else
			{
			
				if( $i >= $limit ) break;
			
				$i++;
			
			}
			
			$call_return['representatives'][$representative]['weight'] = $weight;
			
			if( gmp_cmp( $weight, '0' ) > 0 )
			{
				$call_return['representatives'][$representative]['percent'] = gmp_strval( gmp_div_q( gmp_mul( $weight, '100' ), available_supply ) );
			}
			else
			{
				$call_return['representatives'][$representative]['percent'] = '0';
			}
			
		}
		
		if( !is_null( $nanoconn->error ) )
		{
			$call_return['error'] = no_connection;
		}
	
	}
	
	
	
	// *** Representatives online ***
	
	
	
	elseif( $command == 'representatives_online' )
	{
	
		// Any atleast?
				
		$atleast = isset( $arguments['atleast'] ) ? $arguments['atleast'] : '0';
	
		// Any limit?
			
		$limit = isset( $arguments['count'] ) ? (int) $arguments['count'] : 0;
	
		$representatives_online = $nanoconn->representatives_online( ['weight'=>true] );
		
		uasort( $representatives_online['representatives'], function( $a, $b )
		{
			
			return gmp_cmp( $b['weight'], $a['weight'] );
			
		});
		
		$i = 0;
		
		foreach( $representatives_online['representatives'] as $representative => $data )
		{
			
			if( gmp_cmp( $data['weight'], $atleast ) < 0 ) continue;
			
			if( $limit <= 0 )
			{}
			else
			{
			
				if( $i >= $limit ) break;
			
				$i++;
			
			}
			
			$call_return['representatives_online'][$representative]['weight'] = $data['weight'];
			
			if( gmp_cmp( $data['weight'], '0' ) > 0 )
			{
				$call_return['representatives_online'][$representative]['percent'] = gmp_strval( gmp_div_q( gmp_mul( $data['weight'], '100' ), available_supply ) );
			}
			else
			{
				$call_return['representatives_online'][$representative]['percent'] = '0';
			}
			
		}
		
		if( !is_null( $nanoconn->error ) )
		{
			$call_return['error'] = no_connection;
		}
	
	}
	
	
	
	// *** Print ticker vs favourite currencies ***
	
	
	
	elseif( $command == 'ticker' )
	{
		
		if( !$C['ticker']['enable'] ) exit;
		
		if( isset( $arguments['amount'] ) )
		{
			$call_return['amount'] = $arguments['amount'];
		}
		else
		{
			$call_return['amount'] = NanoTools::raw2['NANO'];
		}
		
	}
	
	
	
	// *** Update ticker ***
	
	
	
	elseif( $command == 'ticker_update' )
	{

		$vs_currency_json = file_get_contents( 'https://api.coingecko.com/api/v3/simple/supported_vs_currencies' );

		if( !$vs_currency_json )
		{
			echo 'ticker_update API #1 Error'; 
			exit;
		}
		
		$vs_currencies_array = json_decode( $vs_currency_json, true );
		
		// Get latest exchange rates vs currencies
		
		$vs_currencies_string = implode( ',', $vs_currencies_array );
		
		$nano_vs_currency_json = file_get_contents( 'https://api.coingecko.com/api/v3/simple/price?ids=nano&vs_currencies=' . $vs_currencies_string . '&include_last_updated_at=true' );
		
		if( !$nano_vs_currency_json )
		{
			echo 'ticker_update API #2 Error'; 
			exit;
		}
		
		$nano_vs_currencies_array = json_decode( $nano_vs_currency_json, true );
		
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
		
		exit;
	
	}
	
	
	
	// *** Update Third Party Tags ***
	
	
	
	elseif( $command == '3tags_update' )
	{
	
		$thirdy_party_tags_elaborated = [];
	
		$third_party_tags_json = file_get_contents( 'https://mynano.ninja/api/accounts/aliases' );
		
		$third_party_tags_array = json_decode( $third_party_tags_json, true );
		
		if( !$third_party_tags_json )
		{
			echo '3tags_update API #1 Error'; 
			exit;
		}
		
		foreach( $third_party_tags_array as $index => $data )
		{
		
			$tag = $data['alias'];
		
			$tag = preg_replace( '/[^a-z0-9. ]+/i', '', $tag );
		
			$tag = str_replace( ' ', '-', $tag );
			
			// $tag = str_replace( '.', '-', $tag );
			
			$tag = strtolower( $tag );
		
			if( $tag == '' ) continue;
		
			$thirdy_party_tags_elaborated['account'][$tag] = $data['account'];
		
		}
		
		ksort( $thirdy_party_tags_elaborated['account'] );
		
		// Save 3tags.json
		
		file_put_contents( thirdtags_file, json_encode( $thirdy_party_tags_elaborated, JSON_PRETTY_PRINT ) );
		
		exit;
	
	}
	
	
	
	// *** Print config.json (except tags) ***
	
	
	
	elseif( $command == 'config' )
	{
		
		$call_return = $C;
		
		unset( $call_return['tags'] );
		
	}
	
	
	
	// *** Print tags ***
	
	
	
	elseif( $command == 'tags' ) 
	{
		
		foreach( $C['tags']['wallet'] as $tag => $id )
		{
			$call_return['wallet'][] = $id;
		}
		
		foreach( $C['tags']['account'] as $tag => $id )
		{
			$call_return['account'][] = $id;
		}
		
		foreach( $C['tags']['block'] as $tag => $id )
		{
			$call_return['block'][] = $id;
		}
		
	}
	
	
	
	// *** Print 3tags ***
	
	
	
	elseif( $command == '3tags' ) 
	{
		
		if( !$C['3tags']['enable'] ) exit;
		
		$thirdtags_array = json_decode( file_get_contents( thirdtags_file ), true );

		foreach( $thirdtags_array['account'] as $tag => $id )
		{
			$call_return['account'][] = $id;
		}
		
	}
	
	
	
	// *** Add new tag ***
	
	
	
	elseif( $command == 'tag_add' )
	{
		
		// Check if cat is defined
		
		if( !isset( $arguments['cat'] ) )
		{
			$call_return['error'] = bad_call;	
		}
		
		// Check if cat is correct
			
		elseif( !array_key_exists( $arguments['cat'], $C['tags'] ) )
		{
			$call_return['error'] = bad_call;	
		}
		
		// Check if tag is defined
		
		elseif( !isset( $arguments['tag'] ) )
		{
			$call_return['error'] = bad_call;	
		}
		
		// Check if value is defined
		
		elseif( !isset( $arguments['value'] ) )
		{
			$call_return['error'] = bad_call;	
		}
		
		// Check if tag is already present
		
		else
		{
		
			if( !array_key_exists( $arguments['tag'], $C['tags'][$arguments['cat']] ) )
			{
			
				$C['tags'][$arguments['cat']][$arguments['tag']] = $arguments['value'];
				
				$call_return[] = $arguments['value'];
				
				file_put_contents( config_file, json_encode( $C, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			
			}
			else
			{
				$call_return['error'] = 'Tag already present';
			}
		
		}
	
	}
	
	
	
	// *** Edit tag ***
	
	
	
	elseif( $command == 'tag_edit' )
	{
		
		// Check if cat is defined
		
		if( !isset( $arguments['cat'] ) )
		{
			$call_return['error'] = bad_call;	
		}
		
		// Check if cat is correct
			
		elseif( !array_key_exists( $arguments['cat'], $C['tags'] ) )
		{
			$call_return['error'] = bad_call;	
		}
		
		// Check if tag is defined
		
		elseif( !isset( $arguments['tag'] ) )
		{
			$call_return['error'] = bad_call;	
		}
		
		// Check if value is defined
		
		elseif( !isset( $arguments['value'] ) )
		{
			$call_return['error'] = bad_call;	
		}
		
		// Check if tag is already present
		
		else
		{
		
			if( array_key_exists( $arguments['tag'], $C['tags'][$arguments['cat']] ) )
			{
			
				$C['tags'][$arguments['cat']][$arguments['tag']] = $arguments['value'];
				
				$call_return[] = $arguments['value'];
				
				file_put_contents( config_file, json_encode( $C, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			
			}
			else
			{
				$call_return['error'] = 'Tag not present';
			}
		
		}
	
	}
	
	
	
	// *** Remove tag ***
	
	
	
	elseif( $command == 'tag_remove' )
	{
		
		// Check if cat is defined
		
		if( !isset( $arguments['cat'] ) )
		{
			$call_return['error'] = bad_call;	
		}
		
		// Check if cat is correct
			
		elseif( !array_key_exists( $arguments['cat'], $C['tags'] ) )
		{
			$call_return['error'] = bad_call;	
		}
		
		// Check if tag is defined
		
		elseif( !isset( $arguments['tag'] ) )
		{
			$call_return['error'] = bad_call;	
		}
		
		// Check if tag is already present
		
		else
		{
		
			if( array_key_exists( $arguments['tag'], $C['tags'][$arguments['cat']] ) )
			{
			
				unset( $C['tags'][$arguments['cat']][$arguments['tag']] );
				
				$call_return[] = $arguments['tag'];
				
				file_put_contents( config_file, json_encode( $C, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
			
			}
			else
			{
				$call_return['error'] = 'Tag not present';
			}
		
		}
	
	}
	
	
	
	// *** Initialize config.json ***
	
	
	
	elseif( $command == 'init' )
	{
		
		echo notable_string( 'Init completed' ) . PHP_EOL;
		
		exit;
		
	}
	
	
	
	// *** Call node ***
	
	
	
	else
	{
		
		$call_return = $nanoconn->{ $command }( $arguments );
		
		if( !is_null( $nanoconn->error ) )
		{
			$call_return['error'] = no_connection;
		}
		
	}
	
	
	
	// *** Check if ticker is updated ***
	
	
	
	if( $C['ticker']['enable'] )
	{
	
		$ticker_delay = time() - ticker_last;
	
		if( $ticker_delay > 60*30 )
		{
			$alerts[] = 'ticker not updated';
		}
	
	}
	
	
	
	
	
	
	// **********************
	// *** Console output ***
	// **********************
	
	
	
	
	
	
	if( $raw_out )
	{
		
		if( count( $alerts ) > 0 ) $call_return['alert'] = $alerts;
		
		echo json_encode( $call_return );
		
	}
	else
	{
	
		$call_return = pretty_array( $call_return );
			
		echo PHP_EOL;
			
		if( count( $alerts ) > 0 ) $call_return['alert'] = $alerts;
			
		echo pretty_print_r( $call_return );

		echo PHP_EOL;
	
	}
	
	
	
	
	
	
	// ****************
	// *** Save log ***
	// ****************
	
	
	
	
	
	
	if( !$no_log )
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
		
			$log_file = log_dir . '/' . date( 'Y-m-d' ) . '.txt';
		
			if( !file_exists( $log_file ) )
			{
				$newline = null;
			}
			else
			{
				$newline = PHP_EOL;
			}
			
			file_put_contents( $log_file, $newline . date( 'm/d/Y H:i:s', time() ) . ' ' . $command . ':' . json_encode( $argv ) . ' ' . json_encode( $call_return ), FILE_APPEND );
		
		}
	
	}
	
	/*
	
		In every loss in every lie
		In every truth that you deny
		And each regret and each goodbye
		Was a mistake too great to hide
		And your voice was all I heard
		That I get what I deserve
	
	*/
	
?>
