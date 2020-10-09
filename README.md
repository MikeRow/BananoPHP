# BananoPHP

PHP libraries and tools for Banano currency

Documentation at [BananoPHP/wiki](https://github.com/MikeRow/BananoPHP/wiki)

---

## Features

- [BananoBlock](https://github.com/MikeRow/BananoPHP/wiki/BananoBlock)

  class for building Banano blocks

- [BananoCLI](https://github.com/MikeRow/BananoPHP/wiki/BananoCLI)

  class for interfacing to Banano node CLI
  
- [BananoIPC](https://github.com/MikeRow/BananoPHP/wiki/BananoIPC)

  class for interfacing to Banano node IPC

- [BananoRPC](https://github.com/MikeRow/BananoPHP/wiki/BananoRPC)

  class for interfacing to Banano node RPC

- [BananoRPCExt](https://github.com/MikeRow/BananoPHP/wiki/BananoRPCExt)

  additional functions for BananoRPC

- [BananoTool](https://github.com/MikeRow/BananoPHP/wiki/BananoTool)

  class for node-independent Banano functions
  
- [BananoWS](https://github.com/MikeRow/BananoPHP/wiki/BananoWS)

  class for interfacing to Banano node WebSocket

---

## To do ...

- Add support to BananoBlock for Epoch v2
- Add support to BananoWS for FlatBuffers
- Increase FlatBuffers performances
- Enable listening on IPC

---

## FAQ

#### How to perform calculations with Banano denominations or raws?

<details><summary>PHP faces troubles when dealing with Banano amounts ...</summary>
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

---

## Credits

Thanks a lot for the work and effort of

- [strawbrary/php-blake2](https://github.com/strawbrary/php-blake2)
- [Textalk/websocket-php](https://github.com/Textalk/websocket-php)
- [google/flatbuffers](https://github.com/google/flatbuffers)
- [Bit-Wasp/bitcoin-lib-php](https://github.com/Bit-Wasp/bitcoin-lib-php)
- [aceat64/EasyBitcoin-PHP](https://github.com/aceat64/EasyBitcoin-PHP)
- [jaimehgb/RaiBlocksPHP](https://github.com/jaimehgb/RaiBlocksPHP)
- [Sergey Kroshnin](https://github.com/SergiySW)

---

## Support

[ban_1mikerow9bqzyqo4ejra6ugr1srerq1egwmacerquch3dz1wry7mkrz4768m](https://creeper.banano.cc/explorer/account/ban_1mikerow9bqzyqo4ejra6ugr1srerq1egwmacerquch3dz1wry7mkrz4768m) <br/>