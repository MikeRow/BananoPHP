# php4nano
PHP libraries and tools for the Nano currency

*Read usage and examples in files header*

## Repository contents

[lib](lib)

<pre>classes for Nano
---
NanoCli.php			interface for nano_node cli commands
NanoRPC.php			interface for RPR commands
NanoRPCExtension.php		extensione for RPC commands
NanoTools.php			usefull tools for Nano
</pre>

[ncm](ncm)

<pre>node CLI manager - exhaustively manage Nano node using bash
---
ncm.php				ncm script
examples			example scripts
</pre>

## Using a script to securely control a node via SSH and ncm

Use a script to start a SSH connection to the server hosting your Nano node and ncm

If you wish to control your node remotely using PHP, I suggest you to use [phpseclib](https://github.com/phpseclib/phpseclib)

Example script: [ncm-remote.php](ncm/examples/ncm-remote.php)

## A clarification about the huge integers

Nano uses huge integers to represent even a tiny amount (1 nano is 10^24 raw), but PHP supports much lower integers

If you need to perform arithmetic operations directly with the raw amounts, I suggest the [GMP library](https://www.php.net/manual/en/book.gmp.php)

You can find denomination/raw conversion functions and other usefull stuffs in [lib/NanoTools.php](lib/NanoTools.php)

## To do

* Add RPC2.0 support
* Add WebSocket lib class
* Add IPC lib class
* Add WebSocket support for ncm
* Add IPC support for ncm

## Discarded

* HTTP Callback support

## Welcome third-party implementations

* HTML/JavaScript ncm visual manager
<pre>HTML/JavaScript visual manager that connects via SSH to a server hosting a Nano node and ncm</pre>

## Credits

* [EasyBitcoin-PHP](https://github.com/aceat64/EasyBitcoin-PHP)
* [coingecko.com](https://www.coingecko.com/en/api)
* [mynano.ninja](https://mynano.ninja/api)

## Support

Don't support me, support Nano decentralization! If you wish to delegate me your weight, here is my representative:
<pre>
nano_1mikerow9bqzyqo4ejra6ugr1srerq1egwmacerquch3dz1wry7mkrz4768m
</pre>