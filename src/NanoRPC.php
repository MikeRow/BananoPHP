<?php

namespace mikerow\php4nano;

use \Exception;

class NanoRPCException extends Exception{}

class NanoRPC
{
    // # Settings
    
    private $protocol;
    private $hostname;
    private $port;
    private $url;
    private $options;
    private $nanoApi;
    private $nanoApiKey;
    private $id = 0;

    
    // # Results and debug

    public $response;
    public $responseRaw;
    public $responseType;
    public $responseTime;
    public $status;
    public $error;
    public $errorCode;
    
    
    // #
    // ## Initialization
    // #
    
    public function __construct(
        string $protocol = 'http',
        string $hostname = 'localhost',
        int    $port     = 7076,
        string $url      = null,
        array  $options  = null
    ) {
        // Protocol
        if ($protocol != 'http' &&
            $protocol != 'https'
        ) {
            throw new NanoRPCException("Invalid protocol: $protocol");
        }
        
        // Url
        if (!empty($url)) {
            if (strpos($url, '/') === 0) {
                $url = substr($url, 1);
            }
        }
        
        $this->protocol = $protocol;
        $this->hostname = $hostname;
        $this->port     = $port;
        $this->url      = $url;
        $this->nanoApi  = 1;
        
        $this->options =
        [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'php4nano/NanoRPC',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_HTTPHEADER     => ['Content-type: application/json']
        ];
        
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $this->options[$key] = $value;
            }
        }
    }
    
    
    // #
    // ## Set Nano API
    // #
    
    public function setNanoApi(int $nano_api)
    {
        if ($nano_api != 1 &&
            $nano_api != 2
        ) {
            throw new NanoRPCException("Invalid Nano API: $nano_api");
        }
        
        $this->nanoApi = $nano_api;
    }
    
    
    // #
    // ## Set Nano API key
    // #
    
    public function setNanoApiKey(string $nano_api_key)
    {
        if (empty($nano_api_key)){
            throw new NanoRPCException("Invalid Nano API key: $nano_api_key");
        }
        
        $this->nanoApiKey = (string) $nano_api_key;
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
        $this->status       = null;
        $this->error        = null;
        $this->errorCode    = null;
        
        
        // # Request: API switch
        
        // v1
        if ($this->nanoApi == 1) {
            $request = $params[0];
            $request['action'] = $method;  
            
        // v2
        } elseif ($this->nanoApi == 2) {
            $request = [
                'correlation_id' => (string) $this->id,
                'message_type'   => $method,
                'message'        => $params[0]
            ];
            
            // Nano API key
            if ($this->nanoApiKey != null) {
                $request['credentials'] = $this->nanoApiKey;
            }
        } else {
            throw new NanoRPCException("Invalid Nano API key");
        }
        
        $request = json_encode($request);
        
        
        // # Build the cURL session
        
        $curl = curl_init("{$this->protocol}://{$this->hostname}:{$this->port}/{$this->url}");
        
        $this->options[CURLOPT_POST]       = true;
        $this->options[CURLOPT_POSTFIELDS] = $request;
        
        
        // # Call
        
        // This prevents users from getting the following warning when open_basedir is set:
        // Warning: curl_setopt() [function.curl-setopt]: CURLOPT_FOLLOWLOCATION cannot be activated when in safe_mode or an open_basedir is set
        if (ini_get('open_basedir')) {
            unset($options[CURLOPT_FOLLOWLOCATION]);
        }
        
        curl_setopt_array($curl, $this->options);

        // Execute the request and decode to an array
        $this->responseRaw = curl_exec($curl); 
        $this->response    = json_decode($this->responseRaw, true);
        
        
        // # Response: API switch
               
        // v1
        if ($this->nanoApi == 1) {
            if (isset($this->response['error'])) {
                $this->error = $this->response['error'];
                $this->response = null;
            }
            
        // v2
        } elseif ($this->nanoApi == 2) {
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
            throw new NanoRPCException("Invalid Nano API key");
        }

        
        // # cURL errors
        
        // If the status is not 200, something is wrong
        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        // If there was no error, this will be an empty string  
        $curl_error = curl_error($curl);

        curl_close($curl);
        
        if (!empty($curl_error)) {
            $this->error = $curl_error;
        }

        if ($this->status != 200) {
            // If node didn't return a nice error message, we need to make our own
            switch ($this->status) {
                case 400:
                    $this->error = 'HTTP_BAD_REQUEST';
                    break;
                    
                case 401:
                    $this->error = 'HTTP_UNAUTHORIZED';
                    break;
                    
                case 403:
                    $this->error = 'HTTP_FORBIDDEN';
                    break;
                    
                case 404:
                    $this->error = 'HTTP_NOT_FOUND';
                    break;
            }
        }

        
        // # Return
        
        if ($this->error) {
            return false;
        } else {
            return $this->response;
        }
    }
}
