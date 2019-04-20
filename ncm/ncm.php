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
			
				Before enabling the ticker option, crontab 'php PATH/php4nano/ncm/ncm.php ticker_update' (I suggest execution every 15 minutes)
				Also, initialize it by executing it manually the first time
			
			- Tags
			
				Do not leave empty tags!
				Only one tag for each wallet/account/block ID

	USAGE:
	
		Default input/output amount denomination in NANO (Mnano)
		
		Input array elements comma separated (you can also use tags)
		
		- Create a shortcut for ncm.php adding to .bashrc:
			
			alias ncm='php PATH/php4nano/ncm/ncm.php'
		
		- Command examples:
		
			// ncm dedicated
		
			ncm init                                                                           init  configuration file
			ncm status                                                                         print  node summary
			ncm wallet_list                                                                    print  all wallets summary
			ncm wallet_info wallet=tag                                                         print  wallet summary (override regular call)
			ncm ticker                                                                         print  latest NANO price compared to favourite vs currencies (if ticker enabled)
			ncm ticker amount=1
			ncm ticker amount=1-USD
			ncm ticker_update                                                                  update ticker.json
			ncm nanoconfig                                                                     print  Nano config.json
			ncm config                                                                         print  ncm config.json (no tags)
			ncm tags                                                                           print  tags
			ncm tag_add cat=account|block|wallet tag=tag value=accountID|blockID|walletID      add    tag
			ncm tag_edit cat=account|block|wallet tag=tag value=accountID|blockID|walletID     edit   tag
			ncm tag_remove cat=account|block|wallet tag=tag                                    remove tag
			
			// Node call
			
			ncm block_count
			ncm wallet_balances wallet=tag
			ncm send wallet=tag1 source=tag2 destination=tag3 amount=1 id=uniqid
			ncm send wallet=tag1 source=tag2 destination=tag3 amount=1-USD id=uniqid (if ticker enabled)
			ncm accounts_balances accounts=tag1,xrb_1nanode8ngaakzbck8smq6ru9bethqwyehomf79sae1k7xd47dkidjqzffeg,tag2 (example of array parameter)
			
			Read full RPC documentation at https://github.com/nanocurrency/nano-node/wiki/RPC-protocol or https://developers.nano.org/docs/rpc
			
			// Node call extension
			
			ncm wallet_wipe wallet=tag1 destination=tag2
			ncm wallet_send wallet=tag1 destination=tag2 amount=1
			ncm wallet_send wallet=tag1 destination=tag2 amount=1-USD (if ticker enabled)
			ncm vanity_account string=test
	
	*/
	
	
	
	
	
	
	// *****************
	// *** Libraries ***
	// *****************
	
	
	
	
	
	
	require_once( __DIR__ . '/../lib/NanoTools.php' );
	
	require_once( __DIR__ . '/../lib/NanoCLI.php' );
	
	require_once( __DIR__ . '/../lib/NanoRPC.php' );
	
	require_once( __DIR__ . '/../lib/NanoRPCExtension.php' );
	
	
	
	
	
	
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
	
	
	
	function custom_number( string $number, int $decimals = null )
	{
		
		global $C;
		
		// $number = sprintf( "%s", $number );
		
		$amount_array = explode( '.', $number );
		
		if( isset( $amount_array[1] ) && $decimals == null )
		{
		
			// Remove useless decimals
		
			while( substr( $amount_array[1], -1 ) == '0' )
			{
				$amount_array[1] = substr( $amount_array[1], 0, -1 );	
			}
			
			if( strlen( $amount_array[1] ) < 1 )
			{
				return number_format( $amount_array[0], 0, '', $C['separator']['thousand'] );
			}
			else
			{
				return number_format( $amount_array[0], 0, '', $C['separator']['thousand'] ) . '.' . $amount_array[1];
			}
				
		}

		return number_format( $number, $decimals, $C['separator']['decimal'], $C['separator']['thousand'] );
	
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
		
		if( is_array( $value ) )
		{
			return $value;
		}
		
		$key_check = explode( '_', $value );
	
		if( array_search( $value, $C['tags']['wallet'] ) ) // Find a wallet tag
		{
			return array_search( $value, $C['tags']['wallet'] ) . $C['separator']['tag'] . $value;
		}
		elseif( isset( $key_check[1] ) && ( $key_check[0] == 'xrb' || $key_check[0] == 'nano' ) ) // Find an account tag
		{

			if( array_search( 'xrb_' . $key_check[1], $C['tags']['account'] ) )
			{
				return array_search( 'xrb_' . $key_check[1], $C['tags']['account'] ) . $C['separator']['tag'] . $value;
			}
			elseif( array_search( 'nano_' . $key_check[1], $C['tags']['account'] ) )
			{
				return array_search( 'nano_' . $key_check[1], $C['tags']['account'] ) . $C['separator']['tag'] . $value;
			}
			else
			{
				return $value;
			}
		
		}
		elseif( array_search( $value, $C['tags']['block'] ) ) // Find a block tag
		{
			return array_search( $value, $C['tags']['block'] ) . $C['separator']['tag'] . $value;
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
					'balance',
					'online_weight_minimum',
					'pending',
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
					'data.ldb',
					'max_size',
					'rotation_size',
					'size'
				];
			
				if( in_array( $key, $check_words ) && is_numeric( $value ) )
				{
					$array[$key] = custom_number( $value/1000000, 0 ) . ' MB';
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
	
	
	
	
	
	
	define( 'data_dir'   , __DIR__ . '/data' );
	
	define( 'log_dir'    , __DIR__ . '/log' );
	
	define( 'config_file', data_dir . '/config.json' );
	
	define(	'ticker_file', data_dir . '/ticker.json' );
	
	define( 'tabulation' , '    ' );
	
	define( 'bad_call'   , 'Bad call' );

	
	
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
			"timestamp": "m/d/Y H:i:s"
		},
		"separator": {
			"decimal": ".",
			"thousand": ",",
			"tag": " : "
		},
		"ticker": {
			"enable": false,
			"fav_vs_currencies": "BTC,USD"
		},
		"tags": {
			"account": {
				"genesis": "xrb_3t6k35gi95xu6tergt6p69ck76ogmitsa8mnijtpxm9fkcm736xtoncuohr3"
			},
			"block": {
				"genesis": "991CF190094C00F0B68E2E5F75F6BEE95A2E0BD93CEAA4A6734DB9F19B728948"
			},
			"wallet": {}
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
	
	
	
	
	
	
	// **********************************
	// *** Check if ticker is updated ***
	// **********************************
	
	
	
	
	
	
	if( $C['ticker']['enable'] )
	{
	
		$ticker_delay = time() - ticker_last;
	
		if( $ticker_delay > 60*30 )
		{
			echo notable_string( 'Ticker is not updated' ) . PHP_EOL;
		}
	
	}
	
	
	
	
	
	
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
		
			$arguments_row_array = [];
			
			$arguments_row_raw = explode( ',', $arguments_row[1] );
			
			// Check if an account tag is available
			
			foreach( $arguments_row_raw as $argument_raw )
			{
		
				if( array_key_exists( $argument_raw, $C['tags']['account'] ) )
				{
					$argument_raw = $C['tags']['account'][$argument_raw];
				}
				
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
		
		$check_words = ['amount'];
		
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
		
		// Summary wallets info
		
		$wallets_count = 0;
		
		$wallets_accounts = 0;
		
		$wallets_balance = 0;
		
		$wallets_pending = 0;
		
		foreach( $C['tags']['wallet'] as $tag => $id )
		{
		
			$wallet_info = $nanoconn->wallet_info( ['wallet'=>$id] );
			
			$wallets_accounts += $wallet_info['accounts_count'];
			
			$wallets_count++;
			
			$wallets_balance = gmp_add( $wallets_balance, $wallet_info['balance'] );
			
			$wallets_pending = gmp_add( $wallets_pending, $wallet_info['pending'] );
		
		}
		
		$wallets_balance = gmp_strval( $wallets_balance );
		
		$wallets_pending = gmp_strval( $wallets_pending );
		
		$call_return['wallets']['balance'] = $wallets_balance;
		
		$call_return['wallets']['pending'] = $wallets_pending;
		
		$call_return['wallets']['count'] = $wallets_count;
		
		$call_return['wallets']['accounts_count'] = $wallets_accounts;
		
	}
	
	
	
	// *** Print wallets info ***
	
	
	
	elseif( $command == 'wallet_list' )
	{ 
			
		$wallet_list = $nanocli->wallet_list();
		
		$wallet_ID = [];
		
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
			
			$wallet_locked = $nanoconn->wallet_locked( ['wallet' => $id] );
			
			$call_return[$id]['balance'] = $wallet_info['balance'];
			
			$call_return[$id]['pending'] = $wallet_info['pending'];
			
			$call_return[$id]['accounts_count'] = $wallet_info['accounts_count'];
			
			$call_return[$id]['locked'] = $wallet_locked['locked'];
			
			// $wallet_balances = $nanoconn->wallet_balances( ['wallet'=>$id] );
			
			// $call_return[$id]['balances'] = $wallet_balances['balances'];
		
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
				$call_return['error'] = 'Bad wallet number';
			}
			else
			{
				$wallet_locked = $nanoconn->wallet_locked( ['wallet' => $arguments['wallet']] );
				
				$call_return[$arguments['wallet']]['balance'] = $wallet_info['balance'];
				
				$call_return[$arguments['wallet']]['pending'] = $wallet_info['pending'];
				
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
			$call_return['error'] = 'Bad wallet number';
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

		if( !$vs_currency_json ) exit; // If error exit
		
		$vs_currencies_array = json_decode( $vs_currency_json, true );
		
		// Get latest exchange rates vs currencies
		
		$vs_currencies_string = implode( ',', $vs_currencies_array );
		
		$nano_vs_currency_json = file_get_contents( 'https://api.coingecko.com/api/v3/simple/price?ids=nano&vs_currencies=' . $vs_currencies_string . '&include_last_updated_at=true' );
		
		if( !$nano_vs_currency_json ) exit; // If error exit
		
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
	
	
	
	// *** Print Nano config.json ***
	
	
	
	elseif( $command == 'nanoconfig' ) 
	{
		$call_return = json_decode( file_get_contents( $C['nano']['data_dir'] . '/config.json' ), true );
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
	}
	
	
	
	
	
	
	// **********************
	// *** Console output ***
	// **********************
	
	
	
	
	
	
	$call_return = pretty_array( $call_return );
	
	echo PHP_EOL;
	
	echo pretty_print_r( $call_return );
	
	echo PHP_EOL;
	
	
	
	
	
	
	// ****************
	// *** Save log ***
	// ****************
	
	
	
	
	
	
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
	
?>