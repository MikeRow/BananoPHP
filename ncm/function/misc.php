<?php 	
	
	use php4nano\lib\NanoTools\NanoTools as NanoTools;
	

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
					$array[$key] = custom_number( NanoTools::raw2den( $value, $C['nano']['denomination'] ), $C['nano']['decimals'], $C['format']['decimal'], $C['format']['thousand'] ) . ' ' . $C['nano']['denomination'];
					
					// If ticker is enabled shows amounts in favourite vs currencies
					
					if( $C['ticker']['enable'] )
					{
						$array[$key] = [];
						$array[$key][] = custom_number( NanoTools::raw2den( $value, $C['nano']['denomination'] ), $C['nano']['decimals'], $C['format']['decimal'], $C['format']['thousand'] ) . ' ' . $C['nano']['denomination'];
						$fav_vs_currencies = explode( ',', $C['ticker']['fav_vs_currencies'] );
						
						foreach( $fav_vs_currencies as $fav_vs_currency )
						{
							if( isset( $C2['vs_currencies'][strtoupper( $fav_vs_currency )] ) )
							{
								$array[$key][] = custom_number( number_format( NanoTools::raw2den( $value, 'NANO' ) * $C2['vs_currencies'][strtoupper( $fav_vs_currency )], 8, '.', '' ), -1, $C['format']['decimal'], $C['format']['thousand'] ) . ' ' . strtoupper( $fav_vs_currency );
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
					$array[$key] = custom_number( $value, 0, $C['format']['decimal'], $C['format']['thousand'] ) . ' s';
				}
				
				if( $key == 'duration' && is_numeric( $value ) ) $array[$key] = custom_number( $value, 0, $C['format']['decimal'], $C['format']['thousand'] ) . ' ms';
				
				if( $key == 'uptime'   && is_numeric( $value ) ) $array[$key] = custom_number( $value / 3600, 2, $C['format']['decimal'], $C['format']['thousand'] ) . ' h';
				
				// Duration exceptions
				
				$check_words =
				[
						'bootstrap_status'
				];
				
				if( in_array( $command, $check_words ) )
				{
					$array['duration'] = custom_number( $value, 0, $C['format']['decimal'], $C['format']['thousand'] ) . ' s';
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
					$array[$key] = custom_number( $value, 0, $C['format']['decimal'], $C['format']['thousand'] );
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
					$array[$key] = custom_number( $value/1000000, 0, $C['format']['decimal'], $C['format']['thousand'] ) . ' MiB';
				}
				
				$check_words =
				[
						'blockchain'
				];
				
				if( in_array( $key, $check_words ) && is_numeric( $value ) )
				{
					$array[$key] = custom_number( $value/1000000, 0, $C['format']['decimal'], $C['format']['thousand'] ) . ' MB';
				}
				
				$check_words =
				[
						'block_average'
				];
				
				if( in_array( $key, $check_words ) && is_numeric( $value ) )
				{
					$array[$key] = custom_number( $value, 0, $C['format']['decimal'], $C['format']['thousand'] ) . ' B';
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

?>