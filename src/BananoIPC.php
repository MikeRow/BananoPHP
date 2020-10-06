<?php

namespace MikeRow\Bandano;

use \Exception;

class BananoIPCException extends Exception{}

class BananoIPC
{
    // * Settings
    
    private $transport;
    private $transportType;  
    private $pathToSocket;
    private $hostname;
    private $port;
    private $listen;
    private $options;
    private $bananoPreamble;
    private $bananoEncoding;
    private $bananoAPIKey;
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
                $this->pathToSocket = '/tmp/banano';
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
            throw new BananoIPCException("Invalid transport type: $transport_type");
        }
        
        $this->transportType = $transport_type;
        $this->bananoEncoding  = 2;
        $this->bananoPreamble  = 'N' . chr($this->bananoEncoding) . chr(0) . chr(0);
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
    // *  Set Banano encoding
    // *
    
    public function setBananoEncoding(int $banano_encoding)
    {
        if ($banano_encoding != 1 &&
            $banano_encoding != 2 &&
            $banano_encoding != 3 &&
            $banano_encoding != 4
        ) {
            throw new BananoIPCException("Invalid Banano encoding: $banano_encoding");
        }
        
        $this->bananoEncoding = $banano_encoding;
        $this->bananoPreamble = 'N' . chr($this->bananoEncoding) . chr(0) . chr(0);
    }
    
    
    // *
    // *  Set Banano API key
    // *
    
    public function setBananoAPIKey(string $banano_api_key)
    {
        if (empty($banano_api_key)){
            throw new BananoIPCException("Invalid Banano API key: $banano_api_key");
        }
        
        $this->bananoAPIKey = (string) $banano_api_key;
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
            throw new BananoIPCException("Invalid transport type");
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
            throw new BananoIPCException("Transport connection is not opened");
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
        // *  Request: Banano encoding switch
        // *        
        
        // * 1/2
        
        if ($this->bananoEncoding == 1 || 
            $this->bananoEncoding == 2
        ) { 
            $request = $params[0];
            $request['action'] = $method;
        
            $request = json_encode($request);
            
            
        // * 3
        
        } elseif ($this->bananoEncoding == 3) {
            if (!class_exists('\\MikeRow\\Bandano\\BananoAPI\\' . $method, true)) {
               $this->error = 'Invalid call';
               return false;
            }
            
            $builder = new \Google\FlatBuffers\FlatbufferBuilder(0);
            
            foreach ($params[0] as $key => $value) {
                $params[0][$key] = $builder->createString($value);
            }
            
            $correlation_id = $builder->createString((string) $this->id);
            $credentials    = $builder->createString($this->bananoAPIKey);
            $message_type   = constant("\\MikeRow\\Bandano\\BananoAPI\\Message::$method");
            
            // Build arguments
            call_user_func_array(
                '\\MikeRow\\Bandano\\BananoAPI\\' . $method . '::start' . $method,
                [$builder]
            );
            
            foreach ($params[0] as $key => $value) {
                if (!method_exists('\\MikeRow\\Bandano\\BananoAPI\\' . $method, 'add' . $key)) {
                    $this->error = 'Invalid call';
                    return false;
                }
                call_user_func_array(
                    '\\MikeRow\\Bandano\\BananoAPI\\' . $method . '::add' . $key,
                    [$builder, $value]
                );
            }
            
            $message = call_user_func_array(
                '\\MikeRow\\Bandano\\BananoAPI\\' . $method . '::end' . $method,
                [$builder]
            );
            
            // Build envelope
            $envelope = \MikeRow\Bandano\BananoAPI\Envelope::createEnvelope(
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
        
        } elseif ($this->bananoEncoding == 4) {         
            $request = [
                'correlation_id' => (string) $this->id,
                'message_type'   => $method,
                'message'        => $params[0]
            ];
            
            // Banano API key
            if ($this->bananoAPIKey != null) {
                $request['credentials'] = $this->bananoAPIKey;
            }
            
            $request = json_encode($request);   
        } else {
            throw new BananoIPCException("Invalid Banano encoding");
        }
        
        $buffer  = $this->bananoPreamble . pack("N", strlen($request)) . $request;
        
        
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
            throw new BananoIPCException("Invalid transport type");
        }
        
        
        // *
        // *  Response: Banano encoding switch
        // *       
        
        // * 1/2
        
        if ($this->bananoEncoding == 1 ||
            $this->bananoEncoding == 2
        ) {
            $this->response = json_decode($this->responseRaw, true);
            
            if (isset($this->response['error'])) {
                $this->error = $this->response['error'];
                $this->response = null;
            }
                
                
        // * 3
            
        } elseif ($this->bananoEncoding == 3) {
            $buffer = \Google\FlatBuffers\ByteBuffer::wrap($this->responseRaw);
            $envelope = \MikeRow\Bandano\BananoAPI\Envelope::getRootAsEnvelope($buffer);
            
            $this->responseType = \MikeRow\Bandano\BananoAPI\Message::Name($envelope->getMessageType());
            $this->responseTime = $envelope->getTime();
            
            if ($envelope->getCorrelationId() != $this->id) {
                $this->error = 'Correlation Id doesn\'t match';
            }
            
            if ($this->responseType == 'Error') {
                $this->error     = $envelope->getMessage(new \MikeRow\Bandano\BananoAPI\Error())->getMessage();
                $this->errorCode = $envelope->getMessage(new \MikeRow\Bandano\BananoAPI\Error())->getCode();
            } else {
                $model = '\\MikeRow\\Bandano\\BananoAPI\\' . $this->responseType;
                
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
            
        } elseif ($this->bananoEncoding == 4) {
            $this->response = json_decode($this->responseRaw, true);
            
            $this->responseType = $this->response['message_type'];
            
            $this->responseTime = (int) $this->response['time'];
            
            if ($this->response['correlation_id'] != $this->id) {
                $this->error = 'Correlation Id doesn\'t match';
            }
            
            if ($this->response['message_type'] == 'Error') {
                $this->error     = $this->response['message'];
                $this->errorCode = (int) $this->response['message']['code'];
                $this->response  = null;
            } else {
                $this->response = $this->response['message'];
            }
        } else {
            throw new BananoIPCException("Invalid Banano encoding");
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
            throw new BananoIPCException("Transport connection is not opened");
        }
        
        // Check if listen is enabled
        if (!$this->listen) {
            throw new BananoIPCException("Listen is not enabled");
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
            throw new BananoIPCException("Invalid transport type");
        }
        
        
        // *
        // *  Response: Banano encoding switch
        // *
        
        // * 1/2
        
        if ($this->bananoEncoding == 1 ||
            $this->bananoEncoding == 2
        ) {
            return json_decode($this->responseRaw, true);
            
            
        // * 3
            
        } elseif ($this->bananoEncoding == 3) {
            return \Google\FlatBuffers\ByteBuffer::wrap($this->responseRaw);
            
            
        // * 4
            
        } elseif ($this->bananoEncoding == 4) {
            return json_decode($this->responseRaw, true);
        } else {
            throw new BananoIPCException("Invalid Banano encoding");
        }
    }
}
