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
    private $id = 0;
   
    
    // #
    // ## Initialization
    // #
    
    public function __construct(string $hostname = 'localhost', int $port = 7078, string $url = null)
    {
        if (strpos($hostname, 'ws://') === 0) {
            $hostname = substr($hostname, 5);
        }
        if (!empty($url)) {
            if (strpos($url, '/') === 0) {
                $url = substr($url, 1);
            }
        }
        
        $this->hostname = $hostname;
        $this->port     = $port;
        $this->url      = $url;
        
        $this->websocket = new \WebSocket\Client("ws://{$this->hostname}:{$this->port}/{$this->url}");
    }

    
    // #
    // ## Close connection
    // #
    
    public function __destruct()
    {
        $this->websocket->close();
    }
    
    
    // #
    // ## Subscribe to topic
    // #
    
    public function subscribe(string $topic, array $options = [], bool $ack = true): int
    {
        if (empty($topic)){
            throw new NanoWSException("Invalid topic: $topic");
        }
        
        $this->id++;
        
        $subscribe = [
            'action' => 'subscribe',
            'topic'  => $topic,
            'id'     => $this->id
        ];
        
        if ($ack) {
            $subscribe['ack'] = true;
        }
        
        if (!empty($options)) {
            $subscribe['options'] = $options;
        }
        
        $subscribe = json_encode($subscribe);
        $this->websocket->send($subscribe);
        
        return $this->id;
    }
    
    
    // #
    // ## Update subscription
    // #
    
    public function update(string $topic, array $options = [], bool $ack = true): int
    {
        if (empty($topic)){
            throw new NanoWSException("Invalid topic: $topic");
        }
        
        $this->id++;
        
        $update = [
            'action' => 'update',
            'topic'  => $topic,
            'id'     => $this->id
        ];
        
        if ($ack) {
            $update['ack'] = true;
        }
        
        $update['options'] = $options;
        
        $update = json_encode($update);
        $this->websocket->send($update);
        
        return $this->id;
    }
    
    
    // #
    // ## Unsubscribe to topic
    // #
    
    public function unsubscribe(string $topic, bool $ack = true): int
    {
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
        while (true) {
            try {
                return json_decode($this->websocket->receive(), true);
            } catch (\WebSocket\ConnectionException $e) {
                //return ['error' => $e];
            }
        }
    }
}
