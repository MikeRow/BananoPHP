# BananoPHP

PHP libraries and tools for Banano currency

Examples at [BananoPHP/test](https://github.com/MikeRow/BananoPHP/tree/master/test)

---

## Features

- [BananoBlock](https://github.com/MikeRow/BananoPHP/blob/master/src/BananoBlock.php)

  class for building Banano blocks

- [BananoCLI](https://github.com/MikeRow/BananoPHP/blob/master/src/BananoCLI.php)

  class for interfacing to Banano node CLI
  
- [BananoIPC](https://github.com/MikeRow/BananoPHP/blob/master/src/BananoIPC.php)

  class for interfacing to Banano node IPC

- [BananoRPC](https://github.com/MikeRow/BananoPHP/blob/master/src/BananoRPC.php)

  class for interfacing to Banano node RPC

- [BananoRPCExt](https://github.com/MikeRow/BananoPHP/blob/master/src/BananoRPCExt.php)

  additional functions for BananoRPC

- [BananoTool](https://github.com/MikeRow/BananoPHP/blob/master/src/BananoTool.php)

  class for node-independent Banano functions
  
- [BananoWS](https://github.com/MikeRow/BananoPHP/blob/master/src/BananoWS.php)

  class for interfacing to Banano node WebSocket

---

## To do ...

- Add Epoch v2 support to BananoBlock
- Add FlatBuffers support to BananoWS
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

[ban_1mikerow9bqzyqo4ejra6ugr1srerq1egwmacerquch3dz1wry7mkrz4768m](https://creeper.banano.cc/explorer/account/ban_1mikerow9bqzyqo4ejra6ugr1srerq1egwmacerquch3dz1wry7mkrz4768m)