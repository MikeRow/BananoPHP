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
	
	function ncmCall( $command, $arguments, $flags )
	{
		
		global $ssh;
	
		$output = $ssh->exec( "php /home/nano/php4nano/ncm/ncm.php " . $command . " '" . json_encode( $arguments ) . "' flags=" . $flags . PHP_EOL );
		
		return json_decode( $output, true );
		
	}
	
	
	
	// Connection



	$ssh = new SSH2( $target );
	
	$key = new RSA();
	
	$key->loadKey( file_get_contents( $privkeyfile_path ) );
	
	if( !$ssh->login( $username, $key ) )
	{
		exit( 'Login Failed' );
	}



	// Execution
	
	$flags = 'raw_in,raw_out,json_in,json_out,no_confirm';
	
	$arguments =
	[
		'account' => 'nano_3t6k35gi95xu6tergt6p69ck76ogmitsa8mnijtpxm9fkcm736xtoncuohr3'
	];
	
	$return = ncmCall( 'account_info', $arguments, $flags );
	
	print_r( $return );
	
	$return = ncmCall( 'status', [], $flags );
	
	print_r( $return );
	
	
	
	// Disconnection
	
	
	
	$ssh->disconnect();

?>