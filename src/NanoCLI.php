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
    public $status;
    
    
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
        $this->response = null;
        $this->status     = null;
        
        $request = ' --' . $method;
        
        if (isset($params[0])) {
            foreach ($params[0] as $key => $value) {
                $request .= ' --' . $key . '=' . $value;
            }
        }
        
        @exec($this->pathToApp . $request . ' 2> /dev/null', $this->response, $this->status);
        
        if ($this->status == 0) {
            return $this->response;
        } else {
            return false;
        }
    }
}
