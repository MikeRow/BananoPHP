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
  
- [NanoRPC2](https://github.com/mikerow/php4nano/wiki/NanoRPC2)

  class for interfacing to Nano node RPC 2.0

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

- Floats aren't very precise at certain decimal depths

  Depending on denomination, you may have more than 30 decimals
  
- Integers can't be bigger than 64bit
  
  Nano uses 128bit for balances/amounts/weights

I suggest to perform calculations in raws using a proper tool

[GNU Multiple Precision](https://www.php.net/manual/en/book.gmp.php) (GMP) is a default PHP extension that fits the job

</p>
</details>

#### Why not use libsodium instead of Salt or php-blake2?

<details><summary>Two limitations prevent the use of libsodium ...</summary>
<p>

- `sodium_crypto_sign_*` use SHA-2 instead Blake2
- `sodium_crypto_generichash_*` don't allow output smaller than 16 bytes

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