# php4nano
PHP libraries and tools for the Nano currency

*Read usage and examples in files header*

## Repository contents

[lib](lib)

<pre>classes for Nano
---
NanoCli.php			interface for nano_node cli commands
NanoRPC.php			interface for RPR commands
NanoRPCExtension.php		extension for RPC commands
NanoTools.php			usefull tools for Nano
</pre>

[ncm](ncm)

<pre>node CLI manager - exhaustively manage Nano node using bash
---
ncm.php				ncm script
examples			example scripts
</pre>

## A clarification about the huge integers

Nano uses huge integers to represent even a tiny amount (1 nano is 10^24 raw), but PHP supports much lower integers<br/>
If you need to perform arithmetic operations directly with the raw amounts, I suggest [GMP](https://www.php.net/manual/en/book.gmp.php) extension

You can find denomination/raw conversion functions and other usefull stuffs in [lib/NanoTools.php](lib/NanoTools.php)

## Using a script to securely control a node via SSH and ncm

Use a script to start a SSH connection to the server hosting your Nano node and ncm

If you wish to control your node remotely using PHP, I suggest [phpseclib](https://github.com/phpseclib/phpseclib) repository<br/>
Try [ncm-remote.php](ncm/examples/ncm-remote.php) example script

## To do

* Add RPC2.0 support
* Add IPC lib class
* Add IPC support for ncm

## Discarded

* HTTP Callback

Out of date, WebSocket are better

* WebSocket

Since their usage is pretty custom in each situation, I've decided to don't develop any class or ncm implementation<br/>
If you are looking for an easy way to implement a WebSocket client for PHP I suggest [Textalk/websocket-php](https://github.com/Textalk/websocket-php) repository

## Welcome third-party implementations

* HTML/JavaScript ncm visual manager

HTML/JavaScript visual manager that connects via SSH to a server hosting a Nano node and ncm

## Credits

* [aceat64/EasyBitcoin-PHP](https://github.com/aceat64/EasyBitcoin-PHP)
* [coingecko.com/API](https://www.coingecko.com/en/api)
* [mynano.ninja/API](https://mynano.ninja/api)

## Support

Don't support me, support Nano decentralization! If you wish to delegate me your weight, here is my representative:
<pre>
nano_1mikerow9bqzyqo4ejra6ugr1srerq1egwmacerquch3dz1wry7mkrz4768m
</pre>