<?php

namespace php4nano;

use \Exception;

class NanoIPCException extends Exception{}

class NanoIPC
{
    // # Settings
    
    private $transportType;
    private $transport;
    private $encoding;
    private $preamble;
    private $pathToSocket;
    private $hostname;
    private $port;
    private $authType;
    private $nanoAPIKey;
    private $id = 0;
   
    
    // # Results and debug
    
    public $response;
    public $responseRaw;
    public $responseType;
    public $responseTime;
    public $error;
    public $errorCode;
    
    
    // #
    // ## Initialization
    // #
    
    public function __construct(string $transport_type, array $params)
    {
        // # Unix domain Socket
        
        if ($transport_type == 'unix_domain_socket') { 
            // Path to socket
            if (!isset($params['path_to_socket']) || !is_string($params['path_to_socket'])) {
                throw new NanoIPCException("Invalid path to socket: " . $params['path_to_socket']);
            }
            
            // Timeout
            if (isset($params['timeout'])) {
                $timeout = (float) $params['timeout'];
            } else {
                $timeout = 15;
            }
            
            // Flags
            if (isset($params['flags'])) {
                $flags = (int) $params['flags'];
            } else {
                $flags = STREAM_CLIENT_CONNECT;
            }
            
            // Context
            if (isset($params['context'])) {
                $context = stream_context_create($params['context']);
            } else {
                $context = stream_context_create([]);
            }
            
            $this->pathToSocket = $params['path_to_socket'];
            $this->transport    = stream_socket_client(
                "unix://{$this->pathToSocket}",
                $this->errorCode,
                $this->error,
                $timeout,
                $flags,
                $context
            );
            if ($this->transport === false) {
                return false;
            }
            
            
        // # TCP
        
        } elseif ($transport_type == 'TCP') {
            // Hostname
            if (!isset($params['hostname']) || !is_string($params['hostname'])) {
                throw new NanoIPCException("Invalid hostname: " . $params['hostname']);
            }
            
            if (strpos($params['hostname'], 'http://') === 0) {
                $params['hostname'] = substr($params['hostname'], 7);
            }
            if (strpos($params['hostname'], 'https://') === 0) {
                $params['hostname'] = substr($params['hostname'], 8);
            }
            
            // Port
            if (!isset($params['port']) || !is_int((int) $params['port'])) {
                throw new NanoIPCException("Invalid port: " . $params['port']);
            }
            
            // Timeout
            if (isset($params['timeout'])) {
                $timeout = (float) $params['timeout'];
            } else {
                $timeout = 15;
            }
            
            // Flags
            if (isset($params['flags'])) {
                $flags = (int) $params['flags'];
            } else {
                $flags = STREAM_CLIENT_CONNECT;
            }
            
            // Context
            if (isset($params['context'])) {
                $context = stream_context_create($params['context']);
            } else {
                $context = stream_context_create([]);
            }
            
            $this->hostname  = $params['hostname'];
            $this->port      = (int) $params['port'];
            $this->transport = stream_socket_client(
                "tcp://{$this->hostname}:{$this->port}",
                $this->errorCode,
                $this->error,
                $timeout,
                $flags,
                $context
            );
            
            
        // #
            
        } else {
            throw new NanoIPCException("Invalid transport type: $transport_type");
        }
        
        $this->transportType = $transport_type;
        $this->encoding      = 4;
        $this->preamble      = 'N' . chr($this->encoding) . chr(0) . chr(0);
    }

    
    // #
    // ## Set encoding
    // #
    
    public function setEncoding(int $encoding)
    {
        if ($encoding != 1 &&
            $encoding != 2 &&
            $encoding != 4
        ) {
            throw new NanoIPCException("Invalid encoding: $encoding");
        }
        
        $this->encoding = $encoding;
        $this->preamble = 'N' . chr($this->encoding) . chr(0) . chr(0);
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
    // ## Call
    // #
    
    public function __call($method, array $params)
    {
        $this->id++;
        $this->response     = null;
        $this->responseRaw  = null;
        $this->responseType = null;
        $this->responseTime = null;
        $this->error        = null;
        $this->errorCode    = null;
        
        
        // # Request
        
        $arguments = [];
 
        if (isset($params[0])) {
            foreach ($params[0] as $key => $value) {
                $arguments[$key] = $value;
            }
        }
        
        
        // # Encoding switch
        
        // 1/2
        if ($this->encoding == 1 || 
            $this->encoding == 2
        ) {
            $request = $arguments;
            $request['action'] = $method;
        // 4
        } elseif ($this->encoding == 4) {
            $request = [
                'correlation_id' => (string) $this->id,
                'message_type'   => $method,
                'message'        => $arguments
            ];
            
            // Nano auth type
            if ($this->authType == 'Nano') {
                $request['credentials'] = $this->nanoAPIkey;
            }
        } else {
            //
        }
        
        $request = json_encode($request);
        $buffer  = $this->preamble . pack("N", strlen($request)) . $request;
        
        
        // # Transport switch
        
        if ($this->transportType == 'unix_domain_socket' ||
            $this->transportType == 'TCP'
        ) {
            // Request
            $socket = fwrite($this->transport, $buffer);
            if ($socket === false) {
                $this->error = 'Unable to send request';
                return false;
            }
            
            // Response lenght
            $size = fread($this->transport, 4);
            if ($size === false) {
                $this->error = 'Unable to receive response lenght';
                return false;
            }
            if (strlen($size) == 0) {
                $this->error = 'Unable to receive response lenght';
                return false;
            }
            
            $size = unpack("N", $size);
            
            // Response
            $this->responseRaw = fread($this->transport, $size[1]);
            if ($this->responseRaw === false) {
                $this->error = 'Unable to receive response';
                return false;
            }
          
            
        // #
        
        } else {
            
        }
        
        $this->response = json_decode($this->responseRaw, true);
        
        
        // # Encoding switch
        
        // 1/2
        if ($this->encoding == 1 ||
            $this->encoding == 2
        ) {
            if (isset($this->response['error'])) {
                $this->error = $this->response['error'];
            }
        // 4
        } elseif ($this->encoding == 4) {
            $this->responseType = $this->response['message_type'];
            
            $this->responseTime = (int) $this->response['time'];
            
            if ((int) $this->response['correlation_id'] != $this->id) {
                $this->error = 'Correlation ID doesn\'t match';
            }
            
            if ($this->response['message_type'] == 'Error') {
                $this->error     = $this->response['message'];
                $this->errorCode = (int) $this->response['message']['code'];
            }
            
            $this->response = $this->response['message'];
        } else {
            //
        }
        
        
        // # Return
        
        if ($this->error) {
            return false;
        } else {
            return $this->response;
        }
    }
}
