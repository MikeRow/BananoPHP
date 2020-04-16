<?php

	/*
	
		Manage a node via SSH using a script and ncm

	*/

	// *** Includes ***



	require_once __DIR__ . '/../lib3/phpseclib_loader.php';

	use phpseclib\Crypt\RSA;
	
	use phpseclib\Net\SSH2;
	
	
	
	// *** Configuration ***
	
	
	
	$target = 'target_host';
	
	$username = 'nano';
	
	$privkeyfile_path = 'path/to/private/key/file';
	
	function ncmCall( $command, $arguments, $flags, $callerID )
	{
		
		global $ssh;
	
		$output = $ssh->exec( "php /home/nano/php4nano/ncm/ncm.php " . $command . " '" . json_encode( $arguments ) . "' flags=" . $flags . " callerID=" . $callerID . PHP_EOL );
		
		return json_decode( $output, true );
		
	}
	
	
	
	// *** Connection ***



	$ssh = new SSH2( $target );
	
	$key = new RSA();
	
	$key->loadKey( file_get_contents( $privkeyfile_path ) );
	
	if( !$ssh->login( $username, $key ) )
	{
		exit( 'Login Failed' );
	}



	// *** Execution ***
	
	
	
	$flags = 'raw_in,raw_out,json_in,json_out,no_confirm';
	
	$callerID = 'remote-script';
	
	$arguments =
	[
		'account' => 'nano_3t6k35gi95xu6tergt6p69ck76ogmitsa8mnijtpxm9fkcm736xtoncuohr3'
	];
	
	$return = ncmCall( 'account_info', $arguments, $flags, $callerID );
	
	print_r( $return );
	
	$return = ncmCall( 'status', [], $flags, $callerID );
	
	print_r( $return );



	// *** Disconnection ***
	
	
	
	$ssh->disconnect();

?>