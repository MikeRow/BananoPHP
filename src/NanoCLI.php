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
    public $code;
    
    
    // #
    // ## Initialization
    // #
    
    public function __construct(string $path_to_app = '/home/nano/nano_node')
    {
        if (!file_exists($path_to_app)) {
            throw new NanoCLIException("Invalid path to app: $path_to_app");
        }
        
        $this->pathToApp = escapeshellarg($path_to_app);
    }

    
    // #
    // ## Call
    // #
    
    public function __call($method, array $params)
    {       
        $this->id++;      
        $this->response = null;
        $this->code     = null;
        
        $request = ' --' . $method;
        
        if (isset($params[0])) {
            foreach ($params[0] as $key => $value) {
                $request .= ' --' . $key . '=' . $value;
            }
        }
            
        exec($this->pathToApp . $request, $this->response, $this->code);
        
        if ($this->code == 0) {
            return $this->response;
        } else {
            return false;
        }
    }
}
