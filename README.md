<p align="center">
	<img width="540" alt="php4nano logo" src="https://raw.githubusercontent.com/mikerow/php4nano/master/logo.png">
</p>

# php4nano
PHP libraries and tools for the Nano currency

<br/>

## Documentation

Read documentation in the [Wiki](https://github.com/mikerow/php4nano/wiki)

<br/>

## Repository contents

<pre>
[examples]                     useful example scripts
---------------------------------------------------------------------------------------
    nano-raws.php              example of mathematical operations with raw amounts
    ncm-remote.php             example of managing a node via SSH using a script and ncm

[lib]                          classes for Nano
---------------------------------------------------------------------------------------
    NanoCli.php                interface for nano_node CLI commands
    NanoRPC.php                interface for RPC commands
    NanoRPCExtension.php       extension commands for NanoRPC.php
    NanoTools.php              usefull node-independent tools for Nano
	
[lib3]                         third-party libraries
---------------------------------------------------------------------------------------
    php-cli-tools-0.11.11      A collection of tools to help with PHP command line utilities
    phpseclib-2.0.27           PHP Secure Communications Library
	php-cli-tools_loader.php   php-cli-tools loader
	phpseclib_loader.php       phpseclib loader

[ncm]                          node CLI manager - exhaustively manage Nano node using bash
---------------------------------------------------------------------------------------
    ncm.php                    ncm script


LICENSE.md                     license
README.md                      (this file)
VERSION.txt                    current version
logo.png                       png logo format
logo.svg                       svg logo format
updates.php                    check for repository updates
</pre>

<br/>

## To do

* RPC 2.0 support (lib,ncm)
* IPC support (lib,ncm)

<br/>

## Discarded

* HTTP Callback

WebSocket is a better solution

* WebSocket

Since usage is pretty custom in each situation, I've decided to don't develop any class or ncm implementation<br/>
If you are looking for an easy way to implement a WebSocket client for PHP I suggest [Textalk/websocket-php](https://github.com/Textalk/websocket-php) repository

<br/>

## Credits

* [aceat64/EasyBitcoin-PHP](https://github.com/aceat64/EasyBitcoin-PHP)
* [wp-cli/php-cli-tools](https://github.com/wp-cli/php-cli-tools)
* [phpseclib/phpseclib](https://github.com/phpseclib/phpseclib)

* [coingecko.com/API](https://www.coingecko.com/en/api)
* [mynano.ninja/API](https://mynano.ninja/api)

Special thanks also to [Sergey Kroshnin](https://github.com/SergiySW) for the prompt support given to me over the months

<br/>

## Support

Don't support me, support Nano decentralization! If you wish to delegate me your weight, here is my representative:
<pre>
nano_1mikerow9bqzyqo4ejra6ugr1srerq1egwmacerquch3dz1wry7mkrz4768m
</pre>