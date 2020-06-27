<?php

namespace php4nano;

use \Exception as Exception;

class NanoRPC2Exception extends Exception{}

class NanoRPC2
{
    // # Settings
    
    private $hostname;
    private $port;
    private $url;
    private $proto;
    private $pathToCACertificate;
    private $requestType;
    private $authType;
    private $username;
    private $password;
    private $nanoAPIKey;

    
    // # Results and debug
    
    public  $status;
    public  $error;
    public  $errorCode;
    public  $responseTime;
    public  $responseRaw;
    public  $response;
    private $id = 0;
    
    
    // #
    // ## Initialization
    // #
    
    public function __construct(string $hostname = 'localhost', string $port = '7076', string $url = 'api/v2')
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
        $this->requestType = 'JSON';
    }
    
    
    // #
    // ## Set request type
    // #
    /*
    public function setRequestType(string $request_type)
    {
        $request_types = [
            'JSON',
            'Flatbuffers'
        ];
        
        if (in_array($request_type, $request_types)) {
            $this->requestType = $request_type;
        } else {
            throw new NanoRPC2Exception("Invalid request type: $request_type");
        }
    }
    */
    
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
            throw new NanoRPC2Exception("Invalid username: $username");
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
            throw new NanoRPC2Exception("Invalid Nano API key: $nano_api_key");
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

        
        // # Build the cURL session
        
        $curl = curl_init("{$this->proto}://{$this->hostname}:{$this->port}/{$this->url}");
        
        $options =
        [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'php4nano/NanoRPC2',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_HTTPHEADER     => ['Content-type: application/json'],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $envelope
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
        
        $response = json_decode($this->responseRaw, true);
        $this->response = $response['message'];

        if (isset($response['time'])) {
            $this->responseTime = $response['time'];
        }
        
        // If the status is not 200, something is wrong
        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        // If there was no error, this will be an empty string  
        $curl_error = curl_error($curl);

        curl_close($curl);
        
        
        // # Errors
        
        if ($response['message_type'] == 'Error') {
            $this->error = $this->response['message'];
            $this->errorCode = $this->response['code'];
        }

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

        if ($this->error) {
            return false;
        } else {
            return $this->response;
        }
    }
}
