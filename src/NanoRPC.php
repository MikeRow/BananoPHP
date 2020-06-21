<?php

namespace php4nano;

use \Exception as Exception;

class NanoRPC
{
    // # Settings
    
    private $hostname;
    private $port;
    private $url;
    private $proto = 'http';
    private $path_to_CACertificate = null;
    private $authType = null;
    private $username = null;
    private $password = null;

    
    // # Results and debug
    
    public  $status;
    public  $error;
    public  $responseRaw;
    public  $response;
    private $id = 0;
    
    
    // #
    // ## Initialization
    // #
    
    public function __construct(string $hostname = 'localhost', string $port = '7076', string $url = null)
    {
        if (strpos($hostname, 'http://') === 0) {
            $hostname = substr($hostname, 7);
        }
        if (strpos($hostname, 'https://') === 0) {
            $hostname = substr($hostname, 8);
        }
        if (strpos($url, '/') === 0) {
            $url = substr($url, 1);
        }
        
        $this->hostname = $hostname;
        $this->port     = $port;
        $this->url      = $url;
    }
    
    
    // #
    // ## Set SSL
    // #
     
    public function setSSL(string $path_to_CACertificate = null)
    {
        $this->proto                 = 'https';
        $this->path_to_CACertificate = $path_to_CACertificate;
    }
    
    
    // #
    // ## Unset SSL
    // #
    
    public function unsetSSL()
    {
        $this->proto                 = 'http';
        $this->path_to_CACertificate = null;
    }
    
    
    // #
    // ## Set basic authentication
    // #
    
    public function setBasicAuth(string $username, string $password = null)
    {
        $this->authType = 'Basic';
        $this->username = $username;
        $this->password = $password;
    }
    
    
    // #
    // ## Unset basic authentication
    // #
    
    public function unsetBasicAuth()
    {
        $this->authType = null;
        $this->username = null;
        $this->password = null;
    }
    
    
    // #
    // ## Call
    // #
    
    public function __call($method, array $params)
    {
        $this->status       = null;
        $this->error        = null;
        $this->node_error   = null;
        $this->responseRaw  = null;
        $this->response     = null;
        $this->id++;
        
        
        // # Action
        
        $request = [
            'action' => $method
        ];
        
        if (isset($params[0])) {
            foreach ($params[0] as $key => $value) {
                $request[$key] = $value;
            }
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
        
        
        // # Auth type
        
        if ($this->authType == 'Basic') {
            $options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
            
            if ($this->username != null) {
                if ($this->password != null) {
                    $options[CURLOPT_USERPWD] = $this->username . ':' . $this->password;
                } else {
                    $options[CURLOPT_USERPWD] = $this->username;
                }
            }
        }

        
        // # HTTPS
        
        if ($this->proto == 'https') {
            // If the CA Certificate was specified we change CURL to look for it
            if ($this->path_to_CACertificate != null) {
                $options[CURLOPT_CAINFO] = $this->path_to_CACertificate;
                $options[CURLOPT_CAPATH] = DIRNAME($this->path_to_CACertificate);
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

        // If the status is not 200, something is wrong
        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        // If there was no error, this will be an empty string  
        $curl_error = curl_error($curl);

        curl_close($curl);
        
        
        // # Errors
        
        if (isset($this->response['error'])) {
            $this->error = $this->response['error'];
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
        }
            
        
        // # Return
        
        return $this->response;
    }
}
