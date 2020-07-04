<?php

namespace php4nano;

use \Exception;

class NanoWSException extends Exception{}

class NanoWS
{
    // # Settings
    
    private $websocket;
    private $hostname;
    private $port;
    private $url;
    private $protocol;
    private $id = 0;
   
    
    // #
    // ## Initialization
    // #
    
    public function __construct(string $hostname = 'localhost', int $port = 7078, string $url = null)
    {
        if (strpos($hostname, 'ws://') === 0) {
            $hostname = substr($hostname, 5);
        }
        if (strpos($hostname, 'wss://') === 0) {
            $hostname = substr($hostname, 6);
        }
        if (!empty($url)) {
            if (strpos($url, '/') === 0) {
                $url = substr($url, 1);
            }
        }
        
        $this->hostname = $hostname;
        $this->port     = $port;
        $this->url      = $url;
        $this->protocol = 'ws';             
    }

    
    // #
    // ## Open connection
    // #
    
    public function open(int $timeout = 5, int $fragment_size = 4096, array $context = null, array $headers = null)
    {  
        $options = [
            'timeout'       => $timeout,
            'fragment_size' => $fragment_size
        ];
        
        if ($context != null) {
            if (is_array($context) && isset($context['ssl'])) {
                $this->protocol = 'wss';
            }
            
            $options['context'] = stream_context_create($context);
        }
        
        if ($headers != null) {
            $options['headers'] = $headers;
        }
        
        try {
            $this->websocket = new \WebSocket\Client("{$this->protocol}://{$this->hostname}:{$this->port}/{$this->url}", $options);
            return true;
        } catch (\WebSocket\ConnectionException $e) {
            $this->websocket = null;
            return $e;
        }
    }
    
    
    // #
    // ## Close connection
    // #
    
    public function close()
    {
        if ($this->websocket != null) {
            $this->websocket->close();
        }
    }
    
    
    // #
    // ## Subscribe to topic
    // #
    
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
    
    
    // #
    // ## Update subscription
    // #
    
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
    
    
    // #
    // ## Unsubscribe to topic
    // #
    
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
    
    
    // #
    // ## Keepalive
    // #
    
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
    
    
    // #
    // ## Listen
    // #
    
    public function listen(): array
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
