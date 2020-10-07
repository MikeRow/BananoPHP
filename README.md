# Bandano

PHP libraries and tools for Banano and Nano currencies

Documentation at [Bandano/wiki](https://github.com/MikeRow/Bandano/wiki)

## Features

|Banano|Nano|Other|
|---|---|---|
|
- [BananoBlock](https://github.com/MikeRow/Bandano/wiki/BananoBlock)

  class for building Banano blocks

- [BananoCLI](https://github.com/MikeRow/Bandano/wiki/BananoCLI)

  class for interfacing to Banano node CLI
  
- [BananoIPC](https://github.com/MikeRow/Bandano/wiki/BananoIPC)

  class for interfacing to Banano node IPC

- [BananoRPC](https://github.com/MikeRow/Bandano/wiki/BananoRPC)

  class for interfacing to Banano node RPC

- [BananoRPCExt](https://github.com/MikeRow/Bandano/wiki/BananoRPCExt)

  additional functions for BananoRPC

- [BananoTool](https://github.com/MikeRow/Bandano/wiki/BananoTool)

  class for node-independent Banano functions
  
- [BananoWS](https://github.com/MikeRow/Bandano/wiki/BananoWS)

  class for interfacing to Banano node WebSocket
|
- [NanoBlock](https://github.com/MikeRow/Bandano/wiki/NanoBlock)

  class for building Nano blocks

- [NanoCLI](https://github.com/MikeRow/Bandano/wiki/NanoCLI)

  class for interfacing to Nano node CLI
  
- [NanoIPC](https://github.com/MikeRow/Bandano/wiki/NanoIPC)

  class for interfacing to Nano node IPC

- [NanoRPC](https://github.com/MikeRow/Bandano/wiki/NanoRPC)

  class for interfacing to Nano node RPC

- [NanoRPCExt](https://github.com/MikeRow/Bandano/wiki/NanoRPCExt)

  additional functions for NanoRPC

- [NanoTool](https://github.com/MikeRow/Bandano/wiki/NanoTool)

  class for node-independent Nano functions
  
- [NanoWS](https://github.com/MikeRow/Bandano/wiki/NanoWS)

  class for interfacing to Nano node WebSocket
|
- [PippinCLI](https://github.com/MikeRow/Bandano/wiki/PippinCLI)

  class for interfacing to Pippin wallet CLI
|

## To do ...

- Add support to BananoBlock/NanoBlock for Epoch v2
- Add support to BananoWS/NanoWS for FlatBuffers
- Increase FlatBuffers performances
- Enable listening on IPC

## FAQ

#### How to perform calculations with Banano/Nano denominations or raws?

<details><summary>PHP faces troubles when dealing with Banano/Nano amounts ...</summary>
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

[ban_1mikerow9bqzyqo4ejra6ugr1srerq1egwmacerquch3dz1wry7mkrz4768m](https://creeper.banano.cc/explorer/account/ban_1mikerow9bqzyqo4ejra6ugr1srerq1egwmacerquch3dz1wry7mkrz4768m) <br/>