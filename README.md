# php4nano
PHP libraries and tools for Nano currency

*Read usage and examples in files header*

## Repository contents

[lib](lib)

<pre>library for Nano</pre>

[ncm](ncm)

<pre>node CLI manager</pre>

## A clarification on the huge integers

Since Nano uses huge integers to represent even a tiny amount (1 nano is 10^24 raw), while PHP supports much lower integers, I suggest you to manage every amount on your database/scripts using NANO or Mnano (10^30 raw) as reference denomination, which involves storing and managing amounts as float

If you need to perform arithmetic operations directly with the raw amounts, I suggest the [GMP library](https://www.php.net/manual/en/book.gmp.php)

You can find denomination/raw conversion functions and other usefull stuffs in [lib/NanoTools.php](lib/NanoTools.php)

## To do

* Add IPC call classes
* Add nano_lib functions as standalone

## Credits

* [aceat64](https://github.com/aceat64): [EasyBitcoin-PHP](https://github.com/aceat64/EasyBitcoin-PHP)
* [coingecko.com](https://www.coingecko.com): [API](https://www.coingecko.com/en/api)
