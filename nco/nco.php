<?php

// *****************
// *** Libraries ***
// *****************



require_once __DIR__ . '/../lib/Tools.php';
require_once __DIR__ . '/../lib3/phpseclib_loader.php';
require_once __DIR__ . '/../lib3/clitable_loader.php';

use php4nano\Tools as NanoTools;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use jc21\CliTable;
use jc21\CliTableManipulator;



// *****************
// *** Functions ***
// *****************



require_once __DIR__ . '/../ncm/function/ksort_recursive.php';
require_once __DIR__ . '/../ncm/function/array_merge_new_recursive.php';
require_once __DIR__ . '/../ncm/function/custom_number.php';
require_once __DIR__ . '/function/ncmCall.php';
require_once __DIR__ . '/function/misc.php';


    
// *********************
// *** Configuration ***
// *********************



define( 'data_dir'              , __DIR__  . '/../../nco' );
define( 'config_file'           , data_dir . '/config.json' );
define( 'nodes_file'            , data_dir . '/nodes.json' );

$C = []; // Configuration
$C2 = []; // Secondary configuration
$arguments = []; // Arguments


// *** Create data folder if not exsist ***


if( !is_dir( data_dir ) )
{
    mkdir( data_dir, 0777, true );
}


// *** config.json model ***


$C_model =
[
    'nano' =>
    [
        'denomination' => 'NANO',
        'decimals'     => 3
    ],
    'wait' => 100,
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



// *******************
// *** Build input ***
// *******************



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


// *** Command and arguments ***


if( count( $argv ) < 2 ) exit;

$command = $argv[1];

unset( $argv[0] );
unset( $argv[1] );

$argv = array_values( $argv );


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
elseif( in_array( $command, ['sync','wallets','node'] ) )
{
    $first_table_display = true;
    
    $last_update = microtime( true );
    
    $ncm_flags = 'raw_in,raw_out,no_log'; // ncmCall default flags

    $ncm_callerID = 'nco'; // ncmCall default callerID
    
    //
    
    echo 'Calling nodes...' . PHP_EOL;
    
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
                $table_data[$tag]['notice'] = '!SSH            ';
                
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
                $table_data[$tag]['notice'] = '!ncm           '; continue;
            }
            
            // Check for alerts
            
            if( isset( $ncmCall['alert'] ) )
            {
                $table_data[$tag]['notice'] = 'Alerts         ';
            }
            else
            {
                $table_data[$tag]['notice'] = 'OK             ';
            }    
            
            
            // *** Call to node ***
            
            
            if( $command == 'sync' )
            {
                // Blocks
                
                $ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'block_count', [], $ncm_flags, $ncm_callerID );
                
                if( !isset( $ncmCall['count'] ) || $ncmCall['count'] == null ) $ncmCall['count'] = 0;
                if( !isset( $ncmCall['unchecked'] ) || $ncmCall['unchecked'] == null ) $ncmCall['unchecked'] = 0;
                if( !isset( $ncmCall['cemented'] ) || $ncmCall['cemented'] == null ) $ncmCall['cemented'] = 0;
                
                $table_data[$tag]['block_count']     = custom_number( $ncmCall['count'], 0, $C['format']['decimal'], $C['format']['thousand'] );
                $table_data[$tag]['block_unchecked'] = custom_number( $ncmCall['unchecked'], 0, $C['format']['decimal'], $C['format']['thousand'] );
                $table_data[$tag]['block_cemented']  = custom_number( $ncmCall['cemented'], 0, $C['format']['decimal'], $C['format']['thousand'] );
                
                // Peers
                
                $ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'peers', [], $ncm_flags, $ncm_callerID );
                
                if( !isset( $ncmCall['peers'] ) || !is_array( $ncmCall['peers'] ) ) $ncmCall['peers'] = [];
                
                $table_data[$tag]['network_peers'] = custom_number( count( $ncmCall['peers'] ), 0, $C['format']['decimal'], $C['format']['thousand'] );    
                
                // Representatives
                
                $ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'representatives_online', [], $ncm_flags, $ncm_callerID );
                
                if( !isset( $ncmCall['count'] ) || $ncmCall['count'] == null ) $ncmCall['count'] = 0;
                if( !isset( $ncmCall['weight'] ) || $ncmCall['weight'] == null ) $ncmCall['weight'] = 0;
                if( !isset( $ncmCall['weight_percent'] ) || $ncmCall['weight_percent'] == null ) $ncmCall['weight_percent'] = 0;
                
                $table_data[$tag]['network_representatives_online'] = custom_number( $ncmCall['count'], 0, $C['format']['decimal'], $C['format']['thousand'] );
                $table_data[$tag]['network_weight_online']          = custom_number( NanoTools::raw2den( $ncmCall['weight'], $C['nano']['denomination'] ), $C['nano']['decimals'], $C['format']['decimal'], $C['format']['thousand'] );
                $table_data[$tag]['network_weight_online_percent']  = custom_number( $ncmCall['weight_percent'], 2, $C['format']['decimal'], $C['format']['thousand'] );    
            }
            
            if( $command == 'wallets' )
            {
                $ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'wallet_list', [], $ncm_flags, $ncm_callerID );
                
                $wallets_balance = '0';
                $wallets_pending = '0';
                $wallets_weight = '0';
                $wallets_count = 0;
                $wallets_accounts_count = 0;
        
                if( is_array( $ncmCall ) )
                {
                    foreach( $ncmCall as $wallet_id => $wallet_info )
                    {
                        if( is_array( $wallet_info ) )
                        {
                            $wallets_count++;
                            $wallets_balance         = gmp_add( $wallets_balance, $wallet_info['balance'] );
                            $wallets_pending         = gmp_add( $wallets_pending, $wallet_info['pending'] );
                            $wallets_weight          = gmp_add( $wallets_weight, $wallet_info['weight'] );
                            $wallets_accounts_count += $wallet_info['accounts_count'];
                        }
                    }
                }
                
                $table_data[$tag]['wallets_balance']        = custom_number( NanoTools::raw2den( gmp_strval( $wallets_balance ), $C['nano']['denomination'] ), $C['nano']['decimals'], $C['format']['decimal'], $C['format']['thousand'] );
                $table_data[$tag]['wallets_pending']        = custom_number( NanoTools::raw2den( gmp_strval( $wallets_pending ), $C['nano']['denomination'] ), $C['nano']['decimals'], $C['format']['decimal'], $C['format']['thousand'] );
                $table_data[$tag]['wallets_weight']         = custom_number( NanoTools::raw2den( gmp_strval( $wallets_weight ), $C['nano']['denomination'] ), $C['nano']['decimals'], $C['format']['decimal'], $C['format']['thousand'] );
                $table_data[$tag]['wallets_count']          = custom_number( $wallets_count, 0, $C['format']['decimal'], $C['format']['thousand'] );
                $table_data[$tag]['wallets_accounts_count'] = custom_number( $wallets_accounts_count, 0, $C['format']['decimal'], $C['format']['thousand'] );
            }
            
            if( $command == 'node' )
            {
                // Version
                
                $ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'version', [], $ncm_flags, $ncm_callerID );
                
                if( !isset( $ncmCall['node_vendor'] ) || $ncmCall['node_vendor'] == null ) $ncmCall['node_vendor'] = '0';
                
                $table_data[$tag]['node_version'] = $ncmCall['node_vendor'];
                
                // Uptime
                
                $ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'uptime', [], $ncm_flags, $ncm_callerID );
                                    
                if( !isset( $ncmCall['seconds'] ) || $ncmCall['seconds'] == null ) $ncmCall['seconds'] = 0;
                
                $table_data[$tag]['node_uptime'] = custom_number( $ncmCall['seconds']/60/60, 2, $C['format']['decimal'], $C['format']['thousand'] ) . ' h';
                
                // Blockchain
                
                $ncmCall = ncmCall( $ssh, $node_data['ncm_path'], 'blockchain', [], $ncm_flags, $ncm_callerID );
                    
                if( !isset( $ncmCall['blockchain'] ) || $ncmCall['blockchain'] == null ) $ncmCall['blockchain'] = 0;
                if( !isset( $ncmCall['block_average'] ) || $ncmCall['block_average'] == null ) $ncmCall['block_average'] = 0;
                
                $table_data[$tag]['node_blockchain']    = custom_number( $ncmCall['blockchain']/1000000, 0, $C['format']['decimal'], $C['format']['thousand'] ) . ' MB';
                $table_data[$tag]['node_block_average'] = custom_number( $ncmCall['block_average'], 0, $C['format']['decimal'], $C['format']['thousand'] ) . ' B';
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
                'middle'       => ' '
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
                
                $table_data[9999]['tag'] = '';
                $table_data[9999]['block_count'] = '            ';
                $table_data[9999]['block_unchecked'] = '            ';
                $table_data[9999]['block_cemented'] = '            ';
                $table_data[9999]['network_peers'] = '      ';
                $table_data[9999]['network_representatives_online'] = '       ';
                $table_data[9999]['network_weight_online'] = '                  ';
                $table_data[9999]['network_weight_online_percent'] = '      ';
                $table_data[9999]['notice'] = '';
            }
            
            if( $command == 'wallets' )
            {
                $table->addField( 'Balance', 'wallets_balance', false );
                $table->addField( 'Pending', 'wallets_pending', false );
                $table->addField( 'Weight', 'wallets_weight', false );
                $table->addField( 'Count', 'wallets_count', false );
                $table->addField( 'Accounts', 'wallets_accounts_count', false );
                
                $table_data[9999]['tag'] = '';
                $table_data[9999]['wallets_balance'] = '                  ';
                $table_data[9999]['wallets_pending'] = '                  ';
                $table_data[9999]['wallets_weight'] = '                  ';
                $table_data[9999]['wallets_count'] = '         ';
                $table_data[9999]['wallets_accounts_count'] = '         ';
                $table_data[9999]['notice'] = '';
            }
            
            if( $command == 'node' )
            {
                $table->addField( 'Version', 'node_version', false );
                $table->addField( 'Uptime', 'node_uptime', false );
                $table->addField( 'Blockchain', 'node_blockchain', false );
                $table->addField( 'Block', 'node_block_average', false );
                
                $table_data[9999]['tag'] = '';
                $table_data[9999]['node_version'] = '           ';
                $table_data[9999]['node_uptime'] = '           ';
                $table_data[9999]['node_blockchain'] = '             ';
                $table_data[9999]['node_block_average'] = '          ';
                $table_data[9999]['notice'] = '';
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
                echo "\033[" . strval( 100 + count( $table_data ) ) . "A";
            }
            
            // Print table
            
            $table->display();
            
            // Print other info
            
            $delay = number_format( microtime( true ) - $last_update - $C['wait']/1000, 3 );
            if( $delay < 0 ) $delay = '0.000';
        
            echo ' '                 . $command;
            echo ' | denomination: ' . $C['nano']['denomination'];
            echo ' | nodes: '        . ( count( $table_data ) - 1 );
            echo ' | wait: '         . number_format( $C['wait']/1000, 3 );
            echo ' | delay: '        . $delay;
            echo PHP_EOL . PHP_EOL;
            
            // Show cursor
            
            fprintf( STDOUT, " \033[?25h" );
        }
        
        if( $flags['no_refresh'] )
        {
            break;
        }
        
        $last_update = microtime( true );
        
        usleep( (int) $C['wait'] * 1000 );
    }
}
else
{
    echo 'Unknown command' . PHP_EOL;  exit;
}
