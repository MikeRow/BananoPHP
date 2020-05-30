<?php

    namespace php4nano\NanoCLI;

    use \Exception as Exception;
    
    class NanoCLI
    {
        // # Settings
        
        private $path_to_app;
        
        
        // # Results and debug
        
        private $id = 0;
        public  $responseRaw;
        public  $response;
        
        
        // #
        // ## Initialization
        // #
        
        public function __construct( string $path_to_app = '/home/nano/nano_node' )
        {
            $this->path_to_app = escapeshellarg( $path_to_app );
        }

        
        // #
        // ## Call
        // #
        
        public function __call( $method, array $params )
        {
            $this->responseRaw = null;
            $this->response    = null;
            $this->id++;
            $request = ' --' . $method;
            
            if( isset( $params[0] ) )
            {
                foreach( $params[0] as $key => $value )
                {
                    $request .= ' --' . $key . '=' . $value;
                }
            }
                
            $this->responseRaw = shell_exec( $this->path_to_app . $request );
                
            if( $this->responseRaw != null )
            {
                $this->response = explode( "\n", $this->responseRaw );
                
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
