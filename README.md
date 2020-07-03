<p align="center">
  <img src="https://github.com/mikerow/php4nano/blob/master/media/logo.png" width="350" alt="php4nano" title="php4nano">
</p>

# php4nano

PHP libraries and tools for the Nano currency

Documentation at [php4nano/wiki](https://github.com/mikerow/php4nano/wiki)

## Features

- [NanoBlock](https://github.com/mikerow/php4nano/wiki/NanoBlock)

  class for building Nano blocks

- [NanoCLI](https://github.com/mikerow/php4nano/wiki/NanoCLI)

  class for interfacing to Nano node CLI
  
- [NanoIPC](https://github.com/mikerow/php4nano/wiki/NanoIPC)

  class for interfacing to Nano node IPC

- [NanoRPC](https://github.com/mikerow/php4nano/wiki/NanoRPC)

  class for interfacing to Nano node RPC

- [NanoRPCExt](https://github.com/mikerow/php4nano/wiki/NanoRPCExt)

  additional functions for NanoRPC

- [NanoTool](https://github.com/mikerow/php4nano/wiki/NanoTool)

  class for node-independent Nano functions
  
- [NanoWS](https://github.com/mikerow/php4nano/wiki/NanoWS)

  class for interfacing to Nano node WebSocket
  
## To do ...

Nothing planned

## FAQ

#### How to perform calculations with Nano denominations or raws?

<details><summary>PHP faces troubles when dealing with Nano amounts ...</summary>
<p>

- Data type `float` isn't precise at certain decimal depths
- Data type `integer` size is limited to 64 bit

A good solution is to perform calculations in raws using [GNU Multiple Precision](https://www.php.net/manual/en/book.gmp.php)

</p>
</details>

#### Why not use libsodium instead of Salt or php-blake2?

<details><summary>Some limitations prevent the use of libsodium ...</summary>
<p>

- Functions `sodium_crypto_sign_*` use SHA-2 instead Blake2
- Functions `sodium_crypto_generichash_*` don't allow output smaller than 16 bytes

</p>
</details>

## Credits

Thanks a lot for the work and effort of

- [devi/Salt](https://github.com/devi/Salt)
- [strawbrary/php-blake2](https://github.com/strawbrary/php-blake2)
- [jaimehgb/RaiBlocksPHP](https://github.com/jaimehgb/RaiBlocksPHP)
- [Textalk/websocket-php](https://github.com/Textalk/websocket-php)
- [aceat64/EasyBitcoin-PHP](https://github.com/aceat64/EasyBitcoin-PHP)
- [FriendsOfPHP/PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
- [squizlabs/PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [Sergey Kroshnin](https://github.com/SergiySW)

## Support

Send funds to [my representative](https://mynano.ninja/account/mikerow)