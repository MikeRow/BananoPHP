<?php

	/*
		Manage a node via SSH using a script and ncm
		
		This script use a custom function "ncmCall" to simplify ncm call procedure
		Some flags like json_in,json_out,no_confirm are set as default due to the nature of the custom function
	*/


	// *** Includes ***


	require_once __DIR__ . '/../lib3/phpseclib_loader.php';

	use phpseclib\Crypt\RSA;
	use phpseclib\Net\SSH2;
	
	
	// *** ncm call ***
	
	
	$ncm_path = '/home/nano/php4nano/ncm/ncm.php';
	
	function ncmCall( &$ssh, string $ncm_path, string $command, array $arguments, string $flags = '', string $callerID = 'remote-script' )
	{
		if( $flags != '' ) $flags .= ',';
		
		$flags .= 'json_in,json_out,no_confirm';
		$return = $ssh->exec( "php $ncm_path $command '" . json_encode( $arguments ) . "' flags=$flags callerID=$callerID" . PHP_EOL );
		
		return json_decode( $return, true );
	}
	
	
	// *** SSH connection ***


	$hostname = 'localhost';
	$username = 'nano';
	$privkeyfile_path = 'path/to/private/key/file';

	//

	$ssh = new SSH2( $hostname );
	$key = new RSA();
	$key->loadKey( file_get_contents( $privkeyfile_path ) );
	
	if( !$ssh->login( $username, $key ) )
	{
		exit( 'Login Failed' );
	}


	// *** Execution ***
	
	
	// Call 1
	
	$arguments =
	[
		'account' => 'nano_3t6k35gi95xu6tergt6p69ck76ogmitsa8mnijtpxm9fkcm736xtoncuohr3'
	];
	
	$ncmCall = ncmCall( $ssh, $ncm_path, 'account_info', $arguments, 'raw_in,raw_out', 'remote-script' );
	
	print_r( $ncmCall );
	
	// Call 2
	
	$ncmCall = ncmCall( $ssh, $ncm_path, 'status', [], 'raw_in,raw_out', 'remote-script' );
	
	print_r( $ncmCall );


	// *** Disconnection ***
	
	
	$ssh->disconnect();

?>