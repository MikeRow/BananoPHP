<?php

namespace MikeRow\NanoPHP;

use \Exception;

class NanoIpcException extends Exception{}

class NanoIpc
{
    // * Settings
    
    private $transport;
    private $transportType;  
    private $pathToSocket;
    private $hostname;
    private $port;
    private $listen;
    private $options;
    private $nanoPreamble;
    private $nanoEncoding;
    private $nanoApiKey;
    private $id = 0;
   
    
    // * Results and debug
    
    public $response;
    public $responseRaw;
    public $responseType;
    public $responseTime;
    public $error;
    public $errorCode;
    
    
    // *
    // *  Initialization
    // *
    
    public function __construct(
        string $transport_type,
        array  $params          = null,
        array  $options         = null
    ) {
        // * Unix domain socket
        
        if ($transport_type == 'unix') { 
            // Path to socket
            if (isset($params[0])) {
                $this->pathToSocket = (string) $params[0];
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
            
            
        // * TCP
        
        } elseif ($transport_type == 'tcp') {
            // Hostname
            if (isset($params[0])) {
                $this->hostname = (string) $params[0];
            } else {
                $this->hostname = 'localhost';
            }
            
            // Port
            if (isset($params[1])) {
                $this->port = (int) $params[1];
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
        } else {
            throw new NanoIpcException("Invalid transport type: $transport_type");
        }
        
        $this->transportType = $transport_type;
        $this->nanoEncoding  = 4;
        $this->nanoPreamble  = 'N' . chr($this->nanoEncoding) . chr(0) . chr(0);
        $this->listen        = false;
    }    
    
    
    // *
    // *  Set listen
    // *  
    
    public function setListen(bool $listen)
    {
        $this->listen = $listen;
    }
    
    
    // *
    // *  Set Nano encoding
    // *
    
    public function setNanoEncoding(int $nano_encoding)
    {
        if ($nano_encoding != 1 &&
            $nano_encoding != 2 &&
            $nano_encoding != 3 &&
            $nano_encoding != 4
        ) {
            throw new NanoIpcException("Invalid Nano encoding: $nano_encoding");
        }
        
        $this->nanoEncoding = $nano_encoding;
        $this->nanoPreamble = 'N' . chr($this->nanoEncoding) . chr(0) . chr(0);
    }
    
    
    // *
    // *  Set Nano API key
    // *
    
    public function setNanoApiKey(string $nano_api_key)
    {
        if (empty($nano_api_key)){
            throw new NanoIpcException("Invalid Nano API key: $nano_api_key");
        }
        
        $this->nanoApiKey = (string) $nano_api_key;
    }
    
    
    // *
    // *  Open connection
    // *
    
    public function open(): bool
    {
        // * Unix domain socket
        
        if ($this->transportType == 'unix') {
            $this->transport = stream_socket_client(
                "unix://{$this->pathToSocket}",
                $this->errorCode,
                $this->error,
                $this->options['timeout'],
                $this->options['flags'],
                $this->options['context']
            );
        
            
        // * TCP    
            
        } elseif ($this->transportType == 'tcp') {
            $this->transport = stream_socket_client(
                "tcp://{$this->hostname}:{$this->port}",
                $this->errorCode,
                $this->error,
                $this->options['timeout'],
                $this->options['flags'],
                $this->options['context']
            );
        } else {
            throw new NanoIpcException("Invalid transport type");
        }
        
        if ($this->transport) {
            return true;
        } else {
            return false;
        }
    }
    
    
    // *
    // *  Close connection
    // *
    
    public function close()
    {
        if ($this->transport != null) {
            stream_socket_shutdown($this->transport, STREAM_SHUT_RDWR);
            $this->transport = null;
        }      
    }
    
    
    // *
    // *  Call
    // *
    
    public function __call($method, array $params)
    {
        // Check transport connection
        if ($this->transport == null) {
            throw new NanoIpcException("Transport connection is not opened");
        }
        
        $this->id++;
        $this->response     = null;
        $this->responseRaw  = null;
        $this->responseType = null;
        $this->responseTime = null;
        $this->error        = null;
        $this->errorCode    = null;
        
        if (!isset($params[0])) {
            $params[0] = [];
        }
        
        
        // *
        // *  Request: Nano encoding switch
        // *        
        
        // * 1/2
        
        if ($this->nanoEncoding == 1 || 
            $this->nanoEncoding == 2
        ) { 
            $request = $params[0];
            $request['action'] = $method;
        
            $request = json_encode($request);
            
            
        // * 3
        
        } elseif ($this->nanoEncoding == 3) {
            if (!class_exists('\\MikeRow\\NanoPHP\\NanoApi\\' . $method, true)) {
               $this->error = 'Invalid call';
               return false;
            }
            
            $builder = new \Google\FlatBuffers\FlatbufferBuilder(0);
            
            foreach ($params[0] as $key => $value) {
                $params[0][$key] = $builder->createString($value);
            }
            
            $correlation_id = $builder->createString((string) $this->id);
            $credentials    = $builder->createString($this->nanoApiKey);
            $message_type   = constant("\\MikeRow\\NanoPHP\\NanoApi\\Message::$method");
            
            // Build arguments
            call_user_func_array(
                '\\MikeRow\\NanoPHP\\NanoApi\\' . $method . '::start' . $method,
                [$builder]
            );
            
            foreach ($params[0] as $key => $value) {
                if (!method_exists('\\MikeRow\\NanoPHP\\NanoApi\\' . $method, 'add' . $key)) {
                    $this->error = 'Invalid call';
                    return false;
                }
                call_user_func_array(
                    '\\MikeRow\\NanoPHP\\NanoApi\\' . $method . '::add' . $key,
                    [$builder, $value]
                );
            }
            
            $message = call_user_func_array(
                '\\MikeRow\\NanoPHP\\NanoApi\\' . $method . '::end' . $method,
                [$builder]
            );
            
            // Build envelope
            $envelope = \MikeRow\NanoPHP\NanoApi\Envelope::createEnvelope(
                $builder,
                null,
                $credentials,
                $correlation_id,
                $message_type,
                $message
            );

            $builder->finish($envelope);
            $request = $builder->sizedByteArray();
        
            
        // * 4
        
        } elseif ($this->nanoEncoding == 4) {         
            $request = [
                'correlation_id' => (string) $this->id,
                'message_type'   => $method,
                'message'        => $params[0]
            ];
            
            // Nano API key
            if ($this->nanoApiKey != null) {
                $request['credentials'] = $this->nanoApiKey;
            }
            
            $request = json_encode($request);   
        } else {
            throw new NanoIpcException("Invalid Nano encoding");
        }
        
        $buffer  = $this->nanoPreamble . pack("N", strlen($request)) . $request;
        
        
        // *
        // *  Request/Response: transport switch
        // *
        
        if ($this->transportType == 'unix' ||
            $this->transportType == 'tcp'
        ) {
            // Request
            $socket = fwrite($this->transport, $buffer);
            if ($socket === false) {
                $this->error = 'Unable to send request';
                return false;
            }
            
            // If listening, skip response
            if ($this->listen) {
                return;
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
        } else {
            throw new NanoIpcException("Invalid transport type");
        }
        
        
        // *
        // *  Response: Nano encoding switch
        // *       
        
        // * 1/2
        
        if ($this->nanoEncoding == 1 ||
            $this->nanoEncoding == 2
        ) {
            $this->response = json_decode($this->responseRaw, true);
            
            if (isset($this->response['error'])) {
                $this->error = $this->response['error'];
                $this->response = null;
            }
                
                
        // * 3
            
        } elseif ($this->nanoEncoding == 3) {
            $buffer = \Google\FlatBuffers\ByteBuffer::wrap($this->responseRaw);
            $envelope = \MikeRow\NanoPHP\NanoApi\Envelope::getRootAsEnvelope($buffer);
            
            $this->responseType = \MikeRow\NanoPHP\NanoApi\Message::Name($envelope->getMessageType());
            $this->responseTime = $envelope->getTime();
            
            if ($envelope->getCorrelationId() != $this->id) {
                $this->error = 'Correlation ID doesn\'t match';
            }
            
            if ($this->responseType == 'Error') {
                $this->error     = $envelope->getMessage(new \MikeRow\NanoPHP\NanoApi\Error())->getMessage();
                $this->errorCode = $envelope->getMessage(new \MikeRow\NanoPHP\NanoApi\Error())->getCode();
            } else {
                $model = '\\MikeRow\\NanoPHP\\NanoApi\\' . $this->responseType;
                
                $methods = get_class_methods($model);
                foreach ($methods as $method) {
                    if (substr($method, 0, 3) == 'get' &&
                        $method != 'getRootAs' . $this->responseType
                    ) {
                        $this->response[substr($method, 3)] = $envelope->getMessage(new $model())->$method();
                    }
                }
            }
            
                
        // * 4
            
        } elseif ($this->nanoEncoding == 4) {
            $this->response = json_decode($this->responseRaw, true);
            
            $this->responseType = $this->response['message_type'];
            
            $this->responseTime = (int) $this->response['time'];
            
            if ($this->response['correlation_id'] != $this->id) {
                $this->error = 'Correlation ID doesn\'t match';
            }
            
            if ($this->response['message_type'] == 'Error') {
                $this->error     = $this->response['message'];
                $this->errorCode = (int) $this->response['message']['code'];
                $this->response  = null;
            } else {
                $this->response = $this->response['message'];
            }
        } else {
            throw new NanoIpcException("Invalid Nano encoding");
        }
        
        
        // * Return
        
        if ($this->error) {
            return false;
        } else {
            return $this->response;
        }
    }
    
    
    // *
    // *  Listen
    // *
    
    public function listen()
    {
        // Check transport connection
        if ($this->transport == null) {
            throw new NanoIpcException("Transport connection is not opened");
        }
        
        
        // *
        // *  Response: transport switch
        // *
        
        if ($this->transportType == 'unix' ||
            $this->transportType == 'tcp'
        ) {
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
        } else {
            throw new NanoIpcException("Invalid transport type");
        }
        
        
        // *
        // *  Response: Nano encoding switch
        // *
        
        // * 1/2
        
        if ($this->nanoEncoding == 1 ||
            $this->nanoEncoding == 2
        ) {
            return json_decode($this->responseRaw, true);
            
            
        // * 3
            
        } elseif ($this->nanoEncoding == 3) {
            return \Google\FlatBuffers\ByteBuffer::wrap($this->responseRaw);
            
            
        // * 4
            
        } elseif ($this->nanoEncoding == 4) {
            return json_decode($this->responseRaw, true);
        } else {
            throw new NanoIpcException("Invalid Nano encoding");
        }
    }
}
