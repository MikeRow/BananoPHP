<p align="center">
	<img width="480" alt="php4nano logo" src="https://raw.githubusercontent.com/mikerow/php4nano/master/media/logo.png">
</p>

# php4nano
PHP libraries and tools for the Nano currency

<br/>

## Documentation

Read installation and usage informations in the [Wiki](https://github.com/mikerow/php4nano/wiki)

<br/>

## Repository contents

<pre>
[examples]                     useful example scripts
--------------------------------------------------------------------------------------------
    nano-raws.php              operations with raw amounts
    ncm-remote.php             managing a node via SSH

[lib]                          library for Nano
--------------------------------------------------------------------------------------------
    NanoCli.php                interface for nano_node CLI
    NanoRPC.php                interface for nano_node RPC
    NanoRPCExtension.php       extension commands for NanoRPC.php
    NanoTools.php              usefull node-independent tools for Nano
	
[lib3]                         third-party libraries
--------------------------------------------------------------------------------------------
    [clitable-1.2]             CLI Table Output for PHP
    [phpseclib-2.0.27]         PHP Secure Communications Library
    clitable_loader.php        clitable loader
    phpseclib_loader.php       phpseclib loader
	
[media]                        media folder
--------------------------------------------------------------------------------------------
    logo.png                   png repository logo format
    logo.svg                   svg repository logo format

[ncm]                          node CLI manager
--------------------------------------------------------------------------------------------
    ncm.php                    ncm script

[nco]                          nodes CLI observer
--------------------------------------------------------------------------------------------
    nco.php                    nco script

LICENSE.md                     repository license
README.md                      (this file)
VERSION.txt                    current repository version
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
- [jc21/clitable](https://github.com/jc21/clitable) <br/>
- [phpseclib/phpseclib](https://github.com/phpseclib/phpseclib) <br/>
- [strawbrary/php-blake2](https://github.com/strawbrary/php-blake2) <br/>
- [coingecko.com/API](https://www.coingecko.com/en/api) <br/>
- [mynano.ninja/API](https://mynano.ninja/api)

Thanks also to [Sergey Kroshnin](https://github.com/SergiySW) for the prompt support given to me over the months

<br/>

## Support

Support Nano decentralization! If you wish to delegate me your weight, here is my representative:
<pre>
nano_1mikerow9bqzyqo4ejra6ugr1srerq1egwmacerquch3dz1wry7mkrz4768m
</pre>