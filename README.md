# php4nano

PHP libraries and tools for the Nano currency

Documentation at [php4nano/wiki](https://github.com/mikerow/php4nano/wiki)

## Features

- NanoBlock

  class for building Nano blocks

- NanoCLI

  class for interfacing to Nano node CLI
  
- NanoIPC

  class for interfacing to Nano node IPC

- NanoRPC

  class for interfacing to Nano node RPC
  
- NanoRPC2

  class for interfacing to Nano node RPC 2.0

- NanoRPCExt

  additional functions for NanoRPC

- NanoTool

  class for node-independent Nano functions
  
## To do ...

(nothing planned)

## FAQ

#### How perform math operations with Nano raws?

<details><summary>Nano deals with huge integers when using raws, so you need an alternative like GMP</summary>
<p>

[GNU Multiple Precision](https://www.php.net/manual/en/book.gmp.php) (GMP) is a default PHP extension that fits the job

</p>
</details>

#### How perform precise math operations with Nano denominations?

<details><summary>PHP floats aren't very precise at certain decimal depths, the ideal is to do math with raws</summary>
<p>

Just convert denomination to raw and then do some math with the proper tool

[GNU Multiple Precision](https://www.php.net/manual/en/book.gmp.php) (GMP) is a default PHP extension that fits the job

</p>
</details>

#### Why not NanoHTTPCallback and NanoWebSocket classes?

<details><summary>HTTP Callback is replaced by WebSocket, which implementation vary depending on the purpose</summary>
<p>

Since WebSocket implementation is quite personalized I decided to don't develop any NanoWebSocket class

I suggest [Textalk/websocket-php](https://github.com/Textalk/websocket-php) repository for implementation

</p>
</details>

#### Why not use libsodium extension?

<details><summary>There are two problems that prevent the use of this extension</summary>
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
- [aceat64/EasyBitcoin-PHP](https://github.com/aceat64/EasyBitcoin-PHP)
- [FriendsOfPHP/PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
- [squizlabs/PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [Sergey Kroshnin](https://github.com/SergiySW)

## Support

Send funds to [my representative](https://mynano.ninja/account/mikerow)