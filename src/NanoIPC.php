<?php

namespace php4nano;

use \Exception;

class NanoIPCException extends Exception{}

class NanoIPC
{
    // # Settings
    
    private $transport;
    private $transportType;  
    private $pathToSocket;
    private $hostname;
    private $port;
    private $options;
    private $nanoPreamble;
    private $nanoEncoding;
    private $nanoApiKey;
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
    
    public function __construct(
        string $transport_type,
        array  $params          = null,
        array  $options         = null
    ) {
        // # Unix domain socket
        
        if ($transport_type == 'unix_domain_socket') { 
            // Path to socket
            if (isset($params['path_to_socket'])) {
                $this->pathToSocket = (string) $params['path_to_socket'];
            } else {
                $this->pathToSocket = '/tmp/nano';
            }
            
            // Timeout
            if (isset($options['timeout'])) {
                $this->options['timeout'] = (float) $options['timeout'];
            } else {
                $this->options['timeout'] = 15;
            }
            
            // Flags
            if (isset($options['flags'])) {
                $this->options['flags'] = (int) $options['flags'];
            } else {
                $this->options['flags'] = STREAM_CLIENT_CONNECT;
            }
            
            // Context
            if (isset($options['context']) && is_array($options['context'])) {
                $this->options['context'] = stream_context_create($options['context']);
            } else {
                $this->options['context'] = stream_context_create([]);
            } 
            
            
        // # TCP
        
        } elseif ($transport_type == 'TCP') {
            // Hostname
            if (isset($params['hostname'])) {
                $this->hostname = (string) $params['hostname'];
            } else {
                $this->hostname = 'localhost';
            }
            
            // Port
            if (isset($params['port'])) {
                $this->port = (int) $params['port'];
            } else {
                $this->port = 7077;
            }
            
            // Timeout
            if (isset($options['timeout'])) {
                $this->options['timeout'] = (float) $options['timeout'];
            } else {
                $this->options['timeout'] = 15;
            }
            
            // Flags
            if (isset($options['flags'])) {
                $this->options['flags'] = (int) $options['flags'];
            } else {
                $this->options['flags'] = STREAM_CLIENT_CONNECT;
            }
            
            // Context
            if (isset($options['context']) && is_array($options['context'])) {
                $this->options['context'] = stream_context_create($options['context']);
            } else {
                $this->options['context'] = stream_context_create([]);
            }
            
            
        // #
            
        } else {
            throw new NanoIPCException("Invalid transport type: $transport_type");
        }
        
        $this->transportType = $transport_type;
        $this->nanoEncoding  = 4;
        $this->nanoPreamble  = 'N' . chr($this->nanoEncoding) . chr(0) . chr(0);
    }    
    
    
    // #
    // ## Set Nano encoding
    // #
    
    public function setNanoEncoding(int $nano_encoding)
    {
        if ($nano_encoding != 1 &&
            $nano_encoding != 2 &&
            $nano_encoding != 4
        ) {
            throw new NanoIPCException("Invalid Nano encoding: $nano_encoding");
        }
        
        $this->nanoEncoding = $nano_encoding;
        $this->nanoPreamble = 'N' . chr($this->nanoEncoding) . chr(0) . chr(0);
    }
    
    
    // #
    // ## Set Nano API key
    // #
    
    public function setNanoApiKey(string $nano_api_key)
    {
        if (empty($nano_api_key)){
            throw new NanoIPCException("Invalid Nano API key: $nano_api_key");
        }
        
        $this->nanoApiKey = $nano_api_key;
    }
    
    
    // #
    // ## Open connection
    // #
    
    public function open()
    {
        // # Unix domain socket
        
        if ($this->transportType == 'unix_domain_socket') {
            $this->transport = stream_socket_client(
                "unix://{$this->pathToSocket}",
                $this->errorCode,
                $this->error,
                $this->options['timeout'],
                $this->options['flags'],
                $this->options['context']
            );
        
            
        // # TCP    
            
        } elseif ($this->transportType == 'TCP') {
            $this->transport = stream_socket_client(
                "tcp://{$this->hostname}:{$this->port}",
                $this->errorCode,
                $this->error,
                $this->options['timeout'],
                $this->options['flags'],
                $this->options['context']
            );
            
        
        // #
            
        } else {
            return false;
        }
        
        if ($this->transport) {
            return true;
        } else {
            return false;
        }
    }
    
    
    // #
    // ## Close connection
    // #
    
    public function close()
    {
        if ($this->transport != null) {
            stream_socket_shutdown($this->transport, STREAM_SHUT_RDWR);
        }      
    }
    
    
    // #
    // ## Call
    // #
    
    public function __call($method, array $params)
    {
        // Check transport connection
        if ($this->transport == null) {
            throw new NanoIPCException("Transport connection is not opened");
        }
        
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
        
        
        // # Nano encoding switch
        
        // 1/2
        if ($this->nanoEncoding == 1 || 
            $this->nanoEncoding == 2
        ) {
            $request = $arguments;
            $request['action'] = $method;
            
        // 4
        } elseif ($this->nanoEncoding == 4) {
            $request = [
                'correlation_id' => (string) $this->id,
                'message_type'   => $method,
                'message'        => $arguments
            ];
            
            // Nano API key
            if ($this->nanoApiKey != null) {
                $request['credentials'] = $this->nanoApiKey;
            }
        } else {
            //
        }
        
        $request = json_encode($request);
        $buffer  = $this->nanoPreamble . pack("N", strlen($request)) . $request;
        
        
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
        
        
        // # Nano encoding switch
        
        // 1/2
        if ($this->nanoEncoding == 1 ||
            $this->nanoEncoding == 2
        ) {
            if (isset($this->response['error'])) {
                $this->error = $this->response['error'];
            }
            
        // 4
        } elseif ($this->nanoEncoding == 4) {
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
