# php4nano
PHP libraries and tools for the Nano currency

<br/>

## Repository contents

[lib](lib)

<pre>classes for Nano
---
NanoCli.php			interface for nano_node CLI commands
NanoRPC.php			interface for RPC commands
NanoRPCExtension.php		extension commands for NanoRPC.php
NanoTools.php			usefull node-independent tools for Nano
</pre>

[ncm](ncm)

<pre>node CLI manager - exhaustively manage Nano node using bash
---
examples			example scripts
ncm.php				ncm script
</pre>

<br/>

## Documentation

Read documentation in the [Wiki](https://github.com/mikerow/php4nano/wiki)

<br/>

## A clarification about the huge integers

Nano uses huge integers to represent even a tiny amount (1 nano is 10^24 raw), but PHP supports much lower integers<br/>
If you need to perform arithmetic operations directly with the raw amounts, I suggest [GMP](https://www.php.net/manual/en/book.gmp.php) extension

You can find denomination/raw conversion functions and other usefull stuffs in [lib/NanoTools.php](lib/NanoTools.php)

<br/>

## Using a script to securely control a node via SSH and ncm

Use a script to start a SSH connection to the server hosting your Nano node and ncm

If you wish to control your node remotely using PHP, I suggest [phpseclib](https://github.com/phpseclib/phpseclib) repository<br/>
Try [ncm-remote.php](ncm/examples/ncm-remote.php) example script

<br/>

## To do

v1.2

* Add RPC 2.0 support (lib,ncm)

v1.3

* Add IPC support (lib,ncm)

<br/>

## Discarded

* HTTP Callback

WebSocket is a better solution

* WebSocket

Since usage is pretty custom in each situation, I've decided to don't develop any class or ncm implementation<br/>
If you are looking for an easy way to implement a WebSocket client for PHP I suggest [Textalk/websocket-php](https://github.com/Textalk/websocket-php) repository

<br/>

## Welcome third-party implementations

* HTML/JavaScript ncm visual manager

HTML/JavaScript visual manager that connects via SSH to a server hosting a Nano node and ncm

<br/>

## Credits

* [aceat64/EasyBitcoin-PHP](https://github.com/aceat64/EasyBitcoin-PHP)
* [coingecko.com/API](https://www.coingecko.com/en/api)
* [mynano.ninja/API](https://mynano.ninja/api)

Special thanks also to [Sergey Kroshnin](https://github.com/SergiySW) for the prompt support given to me over the months

<br/>

## Support

Don't support me, support Nano decentralization! If you wish to delegate me your weight, here is my representative:
<pre>
nano_1mikerow9bqzyqo4ejra6ugr1srerq1egwmacerquch3dz1wry7mkrz4768m
</pre>