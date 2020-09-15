# NanoPHP

PHP libraries and tools for the Nano currency

Documentation at [NanoPHP/wiki](https://github.com/MikeRow/NanoPHP/wiki)

## Features

- [NanoBlock](https://github.com/MikeRow/NanoPHP/wiki/NanoBlock)

  class for building Nano blocks

- [NanoCLI](https://github.com/MikeRow/NanoPHP/wiki/NanoCLI)

  class for interfacing to Nano node CLI
  
- [NanoIPC](https://github.com/MikeRow/NanoPHP/wiki/NanoIPC)

  class for interfacing to Nano node IPC

- [NanoRPC](https://github.com/MikeRow/NanoPHP/wiki/NanoRPC)

  class for interfacing to Nano node RPC

- [NanoRPCExt](https://github.com/MikeRow/NanoPHP/wiki/NanoRPCExt)

  additional functions for NanoRPC

- [NanoTool](https://github.com/MikeRow/NanoPHP/wiki/NanoTool)

  class for node-independent Nano functions
  
- [NanoWS](https://github.com/MikeRow/NanoPHP/wiki/NanoWS)

  class for interfacing to Nano node WebSocket
  
- [PippinCLI](https://github.com/MikeRow/NanoPHP/wiki/PippinCLI)

  class for interfacing to Pippin wallet CLI
  
## To do ...

- Add support to NanoBlock for Epoch v2
- Add support to NanoWS for FlatBuffers
- Increase FlatBuffers performances
- Enable listening on IPC
- Allow different mnemonic words counts

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