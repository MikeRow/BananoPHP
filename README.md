# php4nano

PHP libraries and tools for the Nano currency

Documentation at [php4nano/wiki](https://github.com/mikerow/php4nano/wiki)

## Features

- NanoBlock

  class for building Nano blocks

- NanoCLI

  class for interfacing to Nano node CLI

- NanoRPC

  class for interfacing to Nano node RPC

- NanoRPCExt

  additional functions for NanoRPC

- NanoTool

  class for node-independent Nano functions
  
## To do ...

- additional library class to call node using RPC 2.0
- additional library class to call node using IPC

## FAQ

#### How perform math operations with Nano raws?

<details><summary>Nano deals with huge integers when using raws, for example</summary>
<p>

<pre>
1 NANO = 1 Mnano = 1,000,000 nano = 10^30 raw
</pre>
Since PHP doesn't support mathematical operations with such huge integers, you need an alternative

[GNU Multiple Precision](https://www.php.net/manual/en/book.gmp.php) (GMP) is a default PHP extension that fits the job

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

Send funds or delegate your weight to [my representative](https://mynano.ninja/account/mikerow)