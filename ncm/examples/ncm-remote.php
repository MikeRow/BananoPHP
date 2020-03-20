<?php

	// Download phpseclib library at https://github.com/phpseclib/phpseclib



	// Includes



	include( 'phpseclib/Net/SSH2.php' );
	
	include( 'phpseclib/Crypt/Base.php' );
	
	include( 'phpseclib/Crypt/DES.php' );
	
	include( 'phpseclib/Crypt/RSA.php' );
	
	include( 'phpseclib/Crypt/Hash.php' );
	
	include( 'phpseclib/Crypt/Random.php' );
	
	include( 'phpseclib/Crypt/RC4.php' );
	
	include( 'phpseclib/Crypt/Rijndael.php' );
	
	include( 'phpseclib/Crypt/Twofish.php' );
	
	include( 'phpseclib/Crypt/Blowfish.php' );
	
	include( 'phpseclib/Crypt/TripleDES.php' );
	
	include( 'phpseclib/Math/BigInteger.php' );
	
	use phpseclib\Crypt\RSA;
	
	use phpseclib\Net\SSH2;
	
	
	
	// Configuration
	
	
	
	$target = 'target_host';
	
	$username = 'nano';
	
	$privkeyfile_path = 'path_to_private_key_file';
	
	$ncm_path = 'ncm';
	
	$default_bash = 'default_bash'; // e.g. nano@raspberry:~$
	
	
	
	// Connection



	$ssh = new SSH2( $target );
	
	$key = new RSA();
	
	$key->loadKey( file_get_contents( $privkeyfile_path ) );
	
	if( !$ssh->login( $username, $key ) )
	{
		exit( 'Login Failed' );
	}



	// Execution
	
	
	
	
	$account_genesis = 'nano_3t6k35gi95xu6tergt6p69ck76ogmitsa8mnijtpxm9fkcm736xtoncuohr3';

	$arguments =
	[
		'account' => $account_genesis
	];

	$command = $ncm_path . " account_info '" . json_encode( $arguments ) . "' flags=raw_in,raw_out,json_in,json_out,no_confirm";
	
	$ssh->read( $default_bash );
	
	$ssh->write( $command . "\n" );
	
	$return = $ssh->read( $default_bash );
	
	//echo $return;
	
	$return = explode( "\n", $return );
	
	print_r( json_decode( $return[1], true ) );
	
	
	
	// Disconnection
	
	
	
	$ssh->disconnect();

?>