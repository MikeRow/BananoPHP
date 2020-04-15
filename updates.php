<?php

	$version = file_get_contents( 'VERSION.txt' );

	$options =
	[
		'http' =>
		[
			'method' => "GET",
			'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:74.0) Gecko/20100101 Firefox/74.0\r\n"
		]
	];

	$context = stream_context_create($options);
	
	$php4nano_json = file_get_contents( 'https://api.github.com/repos/mikerow/php4nano/releases/latest', false, $context );
				
	$php4nano_array = json_decode( $php4nano_json, true );

	if( !$php4nano_json || !is_array( $php4nano_array ) || !isset( $php4nano_array['tag_name'] ) )
	{
		echo null . "\n"; exit;
	}

	if( version_compare( str_replace( 'v', '', $version ), str_replace( 'v', '', $php4nano_array['tag_name'] )  ) >= 0 )
	{
		echo false . "\n";
	}
	else
	{
		echo $php4nano_array['tag_name'] . "\n";
	}

?>