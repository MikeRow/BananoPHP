# php4nano
PHP libraries and tools for the Nano currency

*Read usage and examples in files header*

## Repository contents

[lib](lib)

<pre>library for Nano</pre>

[ncm](ncm)

<pre>node CLI manager</pre>

## A clarification about the huge integers

Nano uses huge integers to represent even a tiny amount (1 nano is 10^24 raw), but PHP supports much lower integers

If you need to perform arithmetic operations directly with the raw amounts, I suggest the [GMP library](https://www.php.net/manual/en/book.gmp.php)

You can find denomination/raw conversion functions and other usefull stuffs in [lib/NanoTools.php](lib/NanoTools.php)

## To do

* Add IPC class
* Add IPC support for ncm

## Credits

* [aceat64](https://github.com/aceat64): [EasyBitcoin-PHP](https://github.com/aceat64/EasyBitcoin-PHP)
* [coingecko.com](https://www.coingecko.com): [API](https://www.coingecko.com/en/api)
