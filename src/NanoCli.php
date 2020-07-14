<?php

namespace MikeRow\NanoPHP;

use \Exception;

class NanoCliException extends Exception{}

class NanoCli
{
    // * Settings
    
    private $pathToApp;
    private $id = 0;
    
    
    // * Results and debug
    
    public $response;
    public $status;
    public $error;
    
    
    // *
    // *  Initialization
    // *
    
    public function __construct(string $path_to_app = '/home/nano/nano_node')
    {
        $this->pathToApp = escapeshellarg($path_to_app);
    }

    
    // *
    // *  Call
    // *
    
    public function __call($method, array $params)
    {       
        $this->id++;      
        $this->response = null;
        $this->status   = null;
        $this->error    = null;
        
        $request = ' --' . $method;
        
        if (isset($params[0])) {
            foreach ($params[0] as $key => $value) {
                $request .= ' --' . $key . '=' . $value;
            }
        }
        
        $this->error = exec($this->pathToApp . $request . ' 2>&1', $this->response, $this->status);
        
        if ($this->status == 0) {
            $this->error = null;
            return $this->response;
        } else {
            $this->response = null;
            return false;
        }
    }
}