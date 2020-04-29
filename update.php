<?php

	$version = file_get_contents( 'VERSION.txt' );

	$options =
	[
		'http' =>
		[
			'method' => "GET",
			'header' => "User-Agent: php4nano/update.php\r\n"
		]
	];

	$context = stream_context_create($options);
	
	$php4nano_json = file_get_contents( 'https://api.github.com/repos/mikerow/php4nano/releases/latest', false, $context );	
	$php4nano_array = json_decode( $php4nano_json, true );

	if( !$php4nano_json || !is_array( $php4nano_array ) || !isset( $php4nano_array['tag_name'] ) )
	{
		echo 'Can\'t retrieve latest release' . "\n"; exit;
	}

	if( version_compare( str_replace( 'v', '', $version ), str_replace( 'v', '', $php4nano_array['tag_name'] )  ) >= 0 )
	{
		echo 'Latest realease already installed: ' . $php4nano_array['tag_name'] . "\n";
	}
	else
	{
		echo 'New release found: ' . $php4nano_array['tag_name'] . "\n";
		echo 'Do you want to update? Type \'confirm\' to proceed: ';
		
		$line = stream_get_line( STDIN, 10, PHP_EOL );
				
		if( $line != 'confirm' ) exit;
		
		echo 'Updating...' . "\n";
		shell_exec( 'git checkout ' . $php4nano_array['tag_name'] . ' &' );
	}

?>