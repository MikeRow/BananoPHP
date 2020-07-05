<?php

namespace php4nano;

use \Exception;

class NanoCLIException extends Exception{}

class NanoCLI
{
    // # Settings
    
    private $pathToApp;
    private $id = 0;
    
    
    // # Results and debug
    
    public $response;
    public $responseRaw;
    
    
    // #
    // ## Initialization
    // #
    
    public function __construct(string $path_to_app = '/home/nano/nano_node')
    {
        $this->pathToApp = escapeshellarg($path_to_app);
    }

    
    // #
    // ## Call
    // #
    
    public function __call($method, array $params)
    {
        $this->id++;      
        $this->response    = null;
        $this->responseRaw = null;
        
        $request = ' --' . $method;
        
        if (isset($params[0])) {
            foreach ($params[0] as $key => $value) {
                $request .= ' --' . $key . '=' . $value;
            }
        }
            
        $this->responseRaw = shell_exec($this->pathToApp . $request);
            
        if ($this->responseRaw != null) {
            $this->response = explode("\n", $this->responseRaw);
            
            foreach ($this->response as $key => $value) {
                if ($value == null) {
                    unset($this->response[$key]);
                }
            }
            
            if (count($this->response) < 1) {
                $this->response = null;
            }
            
            return $this->response;
        } else {
            return $this->response;
        }
    }
}
