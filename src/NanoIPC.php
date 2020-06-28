<?php

namespace php4nano;

use \Exception as Exception;

class NanoIPCException extends Exception{}

class NanoIPC
{
    // # Settings
    
    private $transportType;
    private $transport;
    private $transportPreamble;
    private $transportPathToSocket;
    private $transportHostname;
    private $transportPort;
    private $authType;
    private $nanoAPIKey;
    private $id = 0;
   
    
    // # Results and debug
    
    public  $error;
    public  $errorCode;
    public  $responseTime;
    public  $responseRaw;
    public  $response;
    
    
    // #
    // ## Initialization
    // #
    
    public function __construct(string $transport_type, array $params)
    {
        // # Unix domain Socket
        
        if ($transport_type == 'unix_domain_socket') { 
            if (!isset($params['path_to_socket']) || !is_string($params['path_to_socket'])) {
                throw new NanoIPCException("Invalid path to socket: " . $params['path_to_socket']);
            }
            
            $this->transportPathToSocket = $params['path_to_socket'];
            $this->transport     = stream_socket_client(
                "unix://{$this->transportPathToSocket}",
                $this->errorCode,
                $this->error
            );
            if ($this->transport === false) {
                return false;
            }
            
            
        // # TCP
        
        } elseif ($transport_type == 'TCP') {
            if (!isset($params['hostname']) || !is_string($params['hostname'])) {
                throw new NanoIPCException("Invalid hostname: " . $params['hostname']);
            }
            if (!isset($params['port']) || !is_int((int) $params['port'])) {
                throw new NanoIPCException("Invalid port: " . $params['port']);
            }
            
            $this->transportHostname = $params['hostname'];
            $this->transportPort     = (int) $params['port'];
            $this->transport         = stream_socket_client(
                "tcp://{$this->transportHostname}:{$this->transportPort}",
                $this->errorCode,
                $this->error,
                30
            );
            if ($this->transport === false) {
                return false;
            }
            
            
        // #
            
        } else {
            throw new NanoIPCException("Invalid transport type: $transport_type");
        }
        
        $this->transportType     = $transport_type;
        $this->transportPreamble = 'N' . chr(4) . chr(0) . chr(0);
    }

    
    // #
    // ## Set Nano authentication
    // #
    
    public function setNanoAuth(string $nano_api_key = null)
    {
        if (empty($nano_api_key)){
            throw new NanoIPCException("Invalid Nano API key: $nano_api_key");
        }
        
        $this->authType   = 'Nano';
        $this->nanoAPIKey = $nano_api_key;
    }
    
    
    // #
    // ## Unset authentication
    // #
    
    public function unsetAuth()
    {
        $this->authType = null;
    }
    
    
    // #
    // ## Call
    // #
    
    public function __call($method, array $params)
    {
        $this->id++;
        $this->error        = null;
        $this->errorCode    = null;
        $this->responseTime = null;
        $this->responseRaw  = null;
        $this->response     = null;
        
        
        // # Request
        
        $arguments = [];
 
        if (isset($params[0])) {
            foreach ($params[0] as $key => $value) {
                $arguments[$key] = $value;
            }
        }
        
        $envelope = [
            'message_type' => $method,
            'message'      => $arguments
        ];
        
        // Nano auth type
        if ($this->authType == 'Nano') {
            $envelope['credentials'] = $this->nanoAPIkey;
        }

        $envelope = json_encode($envelope);
        $buffer   = $this->transportPreamble . pack("N", strlen($envelope)) . $envelope;
        
        
        // # Unix domain socket, TCP
        
        if ($this->transportType == 'unix_domain_socket' ||
            $this->transportType == 'TCP'
        ) {
            // Request
            $socket = fwrite($this->transport, $buffer);
            if ($socket === false) {
                $this->error = 'Unable to send request';
                return false;
            }
            
            // Response size
            $size = fread($this->transport, 4);
            if ($size === false) {
                $this->error = 'Unable to read response lenght';
                return false;
            }
            if (strlen($size) == 0) {
                $this->error = 'Unable to read response lenght';
                return false;
            }
            
            $size = unpack("N", $size);
            
            // Response
            $this->responseRaw = fread($this->transport, $size[1]);
            if ($this->responseRaw === false) {
                $this->error = 'Unable to read response';
                return false;
            }
          
            
        // #
        
        } else {
            return false;
        }
        
        
        // # Return and errors
        
        $response = json_decode($this->responseRaw, true);
        $this->response = $response['message'];
        
        if (isset($response['time'])) {
            $this->responseTime = $response['time'];
        }
        
        if ($response['message_type'] == 'Error') {
            $this->error = $this->response['message'];
            $this->errorCode = $this->response['code'];
        }
        
        if ($this->error) {
            return false;
        } else {
            return $this->response;
        }
    }
}
