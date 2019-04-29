<?php

	/*

	Read full CLI documentation at https://github.com/nanocurrency/nano-node/wiki/Command-line-interface or https://developers.nano.org/docs/command-line-interface/
	
	USAGE:

	Include NanoCLI.php
		
		require_once('PATH/php4nano/lib/NanoCLI.php');

	Initialize Nano connection/object
	
		$nanocli = new NanoCLI('PATH_TO_NANO_NODE');

	Example of call:

		$args =
		[
			'account' => 'nano_1abcp8j755owefwsxcbww56jqmimsojy1xxduz7m3idze677hkrnjs98da55'
		];
		
		$response = $nanocli->account_key( $args );
		
		print_r( $response );

	*/
	
	
	
	class NanoCLI
	{
		
		// Configuration options
		
		private $path_to_app;

		// Information and debugging
		
		public $response_raw;
		
		public $response;
		
		private $id = 0;
		
		
		
		function __construct( string $path_to_app = './nano_node' )
		{
			
			$this->path_to_app = escapeshellarg( $path_to_app );
			
		}

		function __call( $method, array $params )
		{
			
			$this->response_raw = null;
			
			$this->response = null;

			$this->id++;
			
			$request = ' --' . $method;
			
			if( isset( $params[0] ) )
			{
				
				foreach( $params[0] as $key => $value )
				{
					$request .= ' --' . $key . '=' . $value;
				}
				
			}
				
			$this->response_raw = shell_exec( $this->path_to_app . $request );
				
			if( $this->response_raw != null )
			{
				
				$this->response = explode( "\n", $this->response_raw );
				
				foreach( $this->response as $key => $value )
				{
					if( $value == null ) unset( $this->response[$key] );
				}
					
				return $this->response;
				
			}
			else
			{
				
				$this->response = null;
				
				return $this->response;
				
			}
			
		}
		
	}

?>