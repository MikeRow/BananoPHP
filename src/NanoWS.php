<?php

namespace MikeRow\NanoPHP;

use \Exception;

class NanoWSException extends Exception{}

class NanoWS
{
    // * Settings
    
    private $websocket;
    private $protocol;
    private $hostname;
    private $port;
    private $url;
    private $options;
    private $id = 0;
   
    
    // *
    // *  Initialization
    // *
    
    public function __construct(
        string $protocol = 'ws',
        string $hostname = 'localhost',
        int    $port     = 7078,
        string $url      = null,
        array  $options  = null
    ) {
        // Protocol
        if ($protocol != 'ws' &&
            $protocol != 'wss'
        ) {
            throw new NanoWSException("Invalid protocol: $protocol");
        }
        
        // Url
        if (!empty($url)) {
            if (strpos($url, '/') === 0) {
                $url = substr($url, 1);
            }
        }
        
        $this->options = [];
        
        // Timeout
        if (isset($options['timeout'])) {
            $this->options['timeout'] = (float) $options['timeout'];
        } 
        
        // Fragment size
        if (isset($options['fragment_size'])) {
            $this->options['fragment_size'] = (int) $options['fragment_size'];
        } 
        
        // Context
        if (isset($options['context']) && is_array($options['context'])) {
            $this->options['context'] = stream_context_create($options['context']);
        }
        
        // Headers
        if (isset($options['headers']) && is_array($options['headers'])) {
            $this->options['headers'] = $options['headers'];
        }
        
        $this->protocol = $protocol;  
        $this->hostname = $hostname;
        $this->port     = $port;
        $this->url      = $url;
    }
    
    
    // *
    // *  Open connection
    // *
    
    public function open(): bool
    {   
        try {
            $this->websocket = new \WebSocket\Client("{$this->protocol}://{$this->hostname}:{$this->port}/{$this->url}", $this->options);
            return true;
        } catch (\WebSocket\ConnectionException $e) {
            return false;
        }
    }
    
    
    // *
    // *  Close connection
    // *
    
    public function close()
    {
        if ($this->websocket != null) {
            $this->websocket->close();
            $this->weboscket = null;
        }
    }
    
    
    // *
    // *  Subscribe to topic
    // *
    
    public function subscribe(string $topic, array $options = null, bool $ack = false): int
    {
        // Check WebSocket connection
        if ($this->websocket == null) {
            throw new NanoWSException("WebSocket connection is not opened");
        }
        
        // Check inputs
        if (empty($topic)){
            throw new NanoWSException("Invalid topic: $topic");
        }
        
        $this->id++;
        
        $subscribe = [
            'action' => 'subscribe',
            'topic'  => $topic,
            'id'     => $this->id
        ];
        
        if (!empty($options)) {
            $subscribe['options'] = $options;
        }
        
        if ($ack) {
            $subscribe['ack'] = true;
        }
        
        $subscribe = json_encode($subscribe);
        $this->websocket->send($subscribe);
        
        return $this->id;
    }
    
    
    // *
    // *  Update subscription
    // *
    
    public function update(string $topic, array $options, bool $ack = false): int
    {
        // Check WebSocket connection
        if ($this->websocket == null) {
            throw new NanoWSException("WebSocket connection is not opened");
        }
        
        // Check inputs
        if (empty($topic)){
            throw new NanoWSException("Invalid topic: $topic");
        }
        
        $this->id++;
        
        $update = [
            'action'  => 'update',
            'topic'   => $topic,
            'id'      => $this->id,
            'options' => $options
        ];
        
        if ($ack) {
            $update['ack'] = true;
        }
        
        $update = json_encode($update);
        $this->websocket->send($update);
        
        return $this->id;
    }
    
    
    // *
    // *  Unsubscribe to topic
    // *
    
    public function unsubscribe(string $topic, bool $ack = false): int
    {
        // Check WebSocket connection
        if ($this->websocket == null) {
            throw new NanoWSException("WebSocket connection is not opened");
        }
        
        // Check inputs
        if (empty($topic)){
            throw new NanoWSException("Invalid topic: $topic");
        }
        
        $this->id++;
        
        $unsubscribe = [
            'action' => 'unsubscribe',
            'topic'  => $topic,
            'id'     => $this->id
        ];
        
        if ($ack) {
            $unsubscribe['ack'] = true;
        }
        
        $unsubscribe = json_encode($unsubscribe);
        $this->websocket->send($unsubscribe);
        
        return $this->id;
    }
    
    
    // *
    // *  Keepalive
    // *
    
    public function keepalive(): int
    {
        // Check WebSocket connection
        if ($this->websocket == null) {
            throw new NanoWSException("WebSocket connection is not opened");
        }
        
        $this->id++;
        
        $keepalive = [
            'action' => 'ping',
            'id'     => $this->id
        ];
        
        $keepalive = json_encode($keepalive);
        $this->websocket->send($keepalive);
        
        return $this->id;
    }
    
    
    // *
    // *  Listen
    // *
    
    public function listen()
    {
        // Check WebSocket connection
        if ($this->websocket == null) {
            throw new NanoWSException("WebSocket connection is not opened");
        }
        
        while (true) {
            try {
                return json_decode($this->websocket->receive(), true);
            } catch (\WebSocket\ConnectionException $e) {
                //
            }
        }
    }
}
