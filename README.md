<p align="center">
	<img width="540" alt="php4nano logo" src="https://raw.githubusercontent.com/mikerow/php4nano/master/logo.png">
</p>

# php4nano
PHP libraries and tools for the Nano currency

<br/>

## Documentation

Read install and usage documentation in the [Wiki](https://github.com/mikerow/php4nano/wiki)

<br/>

## Repository contents

<pre>
[examples]                     useful example scripts
--------------------------------------------------------------------------------------------
    nano-raws.php              example of mathematical operations with raw amounts
    ncm-remote.php             example of managing a node via SSH using a script and ncm

[lib]                          library for Nano
--------------------------------------------------------------------------------------------
    NanoCli.php                interface for nano_node CLI commands
    NanoRPC.php                interface for RPC commands
    NanoRPCExtension.php       extension commands for NanoRPC.php
    NanoTools.php              usefull node-independent tools for Nano
	
[lib3]                         third-party libraries
--------------------------------------------------------------------------------------------
    clitable-1.2               CLI Table Output for PHP
    phpseclib-2.0.27           PHP Secure Communications Library
    clitable_loader.php        clitable loader
    phpseclib_loader.php       phpseclib loader

[ncm]                          node CLI manager - exhaustively manage Nano node using bash
--------------------------------------------------------------------------------------------
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

- RPC 2.0 support (lib,ncm)
- IPC support (lib,ncm)

<br/>

## Discarded

- HTTP Callback

WebSocket is a better solution

- WebSocket

Since usage is pretty custom in each situation, I've decided to don't develop any class or ncm implementation<br/>
If you are looking for an easy way to implement a WebSocket client for PHP I suggest [Textalk/websocket-php](https://github.com/Textalk/websocket-php) repository

<br/>

## Credits

- [aceat64/EasyBitcoin-PHP](https://github.com/aceat64/EasyBitcoin-PHP) <br/>
- [wp-cli/php-cli-tools](https://github.com/wp-cli/php-cli-tools) <br/>
- [phpseclib/phpseclib](https://github.com/phpseclib/phpseclib) <br/>
- [coingecko.com/API](https://www.coingecko.com/en/api) <br/>
- [mynano.ninja/API](https://mynano.ninja/api)

Thanks also to [Sergey Kroshnin](https://github.com/SergiySW) for the prompt support given to me over the months

<br/>

## Support

Support Nano decentralization! If you wish to delegate me your weight, here is my representative:
<pre>
nano_1mikerow9bqzyqo4ejra6ugr1srerq1egwmacerquch3dz1wry7mkrz4768m
</pre>