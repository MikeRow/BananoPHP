<?php

namespace php4nano;

use \Exception;

class NanoRPCException extends Exception{}

class NanoRPC
{
    // # Settings
    
    private $hostname;
    private $port;
    private $url;
    private $API;
    private $proto;
    private $pathToCACertificate;
    private $authType;
    private $username;
    private $password;
    private $nanoAPIKey;
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
    
    public function __construct(string $hostname = 'localhost', int $port = 7076, string $url = null)
    {
        if (strpos($hostname, 'http://') === 0) {
            $hostname = substr($hostname, 7);
        }
        if (strpos($hostname, 'https://') === 0) {
            $hostname = substr($hostname, 8);
        }
        if (!empty($url)) {
            if (strpos($url, '/') === 0) {
                $url = substr($url, 1);
            }
        }
        
        $this->hostname    = $hostname;
        $this->port        = $port;
        $this->url         = $url;
        $this->proto       = 'http';
        $this->API         = 1;
    }
    
    
    // #
    // ## Set API
    // #
    
    public function setAPI(int $API)
    {
        if ($API != 1 &&
            $API != 2
        ) {
            throw new NanoRPCException("Invalid API: $API");
        }
        
        $this->API = $API;
    }
    
    
    // #
    // ## Set SSL
    // #
     
    public function setSSL(string $path_to_CACertificate = null)
    {
        $this->proto               = 'https';
        $this->pathToCACertificate = $path_to_CACertificate;
    }
    
    
    // #
    // ## Unset SSL
    // #
    
    public function unsetSSL()
    {
        $this->proto               = 'http';
        $this->pathToCACertificate = null;
    }
    
    
    // #
    // ## Set basic authentication
    // #
    
    public function setBasicAuth(string $username, string $password = null)
    {
        if (empty($username)){
            throw new NanoRPCException("Invalid username: $username");
        }
        
        $this->authType = 'Basic';
        $this->username = $username;
        $this->password = $password;
    }
    
    
    // #
    // ## Set Nano authentication
    // #
    
    public function setNanoAuth(string $nano_api_key = null)
    {
        if (empty($nano_api_key)){
            throw new NanoRPCException("Invalid Nano API key: $nano_api_key");
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
        $this->response     = null;
        $this->responseRaw  = null;
        $this->responseType = null;
        $this->responseTime = null;
        $this->status       = null;
        $this->error        = null;
        $this->errorCode    = null;
        
        
        // # Request
        
        $arguments = [];
        
        if (isset($params[0])) {
            foreach ($params[0] as $key => $value) {
                $arguments[$key] = $value;
            }
        }
        
        
        // # API switch
        
        // v1
        if ($this->API == 1) {
            $request = $arguments;
            $request['action'] = $method;   
        // v2
        } elseif ($this->API == 2) {
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

        
        // # Build the cURL session
        
        $curl = curl_init("{$this->proto}://{$this->hostname}:{$this->port}/{$this->url}");
        
        $options =
        [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'php4nano/NanoRPC',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_HTTPHEADER     => ['Content-type: application/json'],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $request
        ];
        
        
        // # cURL auth type
        
        if ($this->authType == 'Basic') {
            $options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
            
            if ($this->username != null) {
                if ($this->password != null) {
                    $options[CURLOPT_USERPWD] = $this->username . ':' . $this->password;
                } else {
                    $options[CURLOPT_USERPWD] = $this->username;
                }
            }
        } else {
            // No auth
        }

        
        // # HTTPS
        
        if ($this->proto == 'https') {
            // If the CA Certificate was specified we change CURL to look for it
            if ($this->pathToCACertificate != null) {
                $options[CURLOPT_CAINFO] = $this->pathToCACertificate;
                $options[CURLOPT_CAPATH] = DIRNAME($this->pathToCACertificate);
            } else {
                $options[CURLOPT_SSL_VERIFYPEER] = false;
            }
        }

        
        // # Call
        
        // This prevents users from getting the following warning when open_basedir is set:
        // Warning: curl_setopt() [function.curl-setopt]: CURLOPT_FOLLOWLOCATION cannot be activated when in safe_mode or an open_basedir is set
        if (ini_get('open_basedir')) {
            unset($options[CURLOPT_FOLLOWLOCATION]);
        }
        
        curl_setopt_array($curl, $options);

        // Execute the request and decode to an array
        $this->responseRaw = curl_exec($curl); 
        $this->response    = json_decode($this->responseRaw, true);
        
        
        // # API switch
        
        // v1
        if ($this->API == 1) {
            if (isset($this->response['error'])) {
                $this->error = $this->response['error'];
            }
        // v2
        } elseif ($this->API == 2) {
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
