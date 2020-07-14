# NanoPHP

PHP libraries and tools for the Nano currency

Documentation at [NanoPHP/wiki](https://github.com/MikeRow/NanoPHP/wiki)

## Features

- [NanoBlock](https://github.com/MikeRow/NanoPHP/wiki/NanoBlock)

  class for building Nano blocks

- [NanoCli](https://github.com/MikeRow/NanoPHP/wiki/NanoCli)

  class for interfacing to Nano node CLI
  
- [NanoIpc](https://github.com/MikeRow/NanoPHP/wiki/NanoIpc)

  class for interfacing to Nano node IPC

- [NanoRpc](https://github.com/MikeRow/NanoPHP/wiki/NanoRpc)

  class for interfacing to Nano node RPC

- [NanoRpcExt](https://github.com/MikeRow/NanoPHP/wiki/NanoRpcExt)

  additional functions for NanoRpc

- [NanoTool](https://github.com/MikeRow/NanoPHP/wiki/NanoTool)

  class for node-independent Nano functions
  
- [NanoWs](https://github.com/MikeRow/NanoPHP/wiki/NanoWs)

  class for interfacing to Nano node WebSocket
  
- [PippinCli](https://github.com/MikeRow/NanoPHP/wiki/PippinCli)

  class for interfacing to Pippin wallet CLI
  
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

- [strawbrary/php-blake2](https://github.com/strawbrary/php-blake2)
- [Textalk/websocket-php](https://github.com/Textalk/websocket-php)
- [google/flatbuffers](https://github.com/google/flatbuffers)
- [Bit-Wasp/bitcoin-lib-php](https://github.com/Bit-Wasp/bitcoin-lib-php)
- [aceat64/EasyBitcoin-PHP](https://github.com/aceat64/EasyBitcoin-PHP)
- [Sergey Kroshnin](https://github.com/SergiySW)

## Support

Send funds to [my representative](https://mynano.ninja/account/mikerow)