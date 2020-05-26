<?php

	namespace php4nano\lib\NanoCLI;

	class NanoCLI
	{
		// *** Configuration options ***
		
		
		private $path_to_app;
		
		
		// *** Information and debugging ***
		
		
		public $response_raw;
		public $response;
		private $id = 0;
		
		
		// *** Initialization ***
		
		
		public function __construct( string $path_to_app = '/home/nano/nano_node' )
		{
			if( !file_exists( $path_to_app ) ) throw new Exception( "Invalid nano_node path: $path_to_app" );
			
			$this->path_to_app = escapeshellarg( $path_to_app );
		}

		
		// *** Call ***
		
		
		public function __call( $method, array $params )
		{
			$this->response_raw = null;
			$this->response     = null;
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