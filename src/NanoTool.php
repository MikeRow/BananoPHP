<?php

namespace MikeRow\NanoPHP;

use \Exception;
use \SplFixedArray;
use BitWasp\BitcoinLib\BIP39;
use MikeRow\PHPUtils as utils;
use MikeRow\NanoSalt\Blake2b\Blake2b;
use MikeRow\NanoSalt\Ed25519\Ed25519;
use MikeRow\NanoSalt\Salt;
use MikeRow\NanoSalt\FieldElement;

class NanoToolException extends Exception{}

class NanoTool
{
    // *
    // *  Constants
    // *
    
    const RAWS = [
        'unano' =>                '1000000000000000000',
        'mnano' =>             '1000000000000000000000',
         'nano' =>          '1000000000000000000000000',
        'knano' =>       '1000000000000000000000000000',
        'Mnano' =>    '1000000000000000000000000000000',
         'NANO' =>    '1000000000000000000000000000000',
        'Gnano' => '1000000000000000000000000000000000'
    ];
    
    const PREAMBLE = '0000000000000000000000000000000000000000000000000000000000000006';
    const EMPTY32  = '0000000000000000000000000000000000000000000000000000000000000000';
    const HARDENED =  0x80000000;
       
    
    // *
    // *  Denomination to raw
    // *
    
    public static function den2raw($amount, string $denomination): string
    {
        if (!array_key_exists($denomination, self::RAWS)) {
            throw new NanoToolException("Invalid denomination: $denomination");
        }
        
        $raw_to_denomination = self::RAWS[$denomination];
        
        if ($amount == 0) {
            return '0';
        }
        
        if (strpos($amount, '.')) {
            $dot_pos = strpos($amount, '.');
            $number_len = strlen($amount) - 1;
            $raw_to_denomination = substr($raw_to_denomination, 0, -($number_len - $dot_pos));
        }
        
        $amount = str_replace('.', '', $amount) . str_replace('1', '', $raw_to_denomination);
        
        // Remove useless zeros from left
        while (substr($amount, 0, 1) == '0') {
            $amount = substr($amount, 1);
        }
        
        return $amount;
    }


    // *
    // *  Raw to denomination
    // *
    
    public static function raw2den(string $amount, string $denomination): string
    {
        if (!array_key_exists($denomination, self::RAWS)) {
            throw new NanoToolException("Invalid denomination: $denomination");
        }
        
        $raw_to_denomination = self::RAWS[$denomination];
        
        if ($amount == '0') {
            return 0;
        }
        
        $prefix_lenght = 39 - strlen($amount);
        
        $i = 0;
        
        while ($i < $prefix_lenght) {
            $amount = '0' . $amount;
            $i++;
        }
        
        $amount = substr_replace($amount, '.', -(strlen($raw_to_denomination)-1), 0);
    
        // Remove useless zeroes from left
        while (substr($amount, 0, 1) == '0' && substr($amount, 1, 1) != '.') {
            $amount = substr($amount, 1);
        }
    
        // Remove useless decimals
        while (substr($amount, -1) == '0') {
            $amount = substr($amount, 0, -1);
        }
        
        // Remove dot if all decimals are zeros
        if (substr($amount, -1) == '.') {
            $amount = substr($amount, 0, -1);
        }
    
        return $amount;
    }
    
    
    // *
    // *  Denomination to denomination
    // *
    
    public static function den2den($amount, string $denomination_from, string $denomination_to): string
    {
        if (!array_key_exists($denomination_from, self::RAWS)) {
            throw new NanoToolException("Invalid source denomination: $denomination_from");
        }
        if (!array_key_exists($denomination_to, self::RAWS)) {
            throw new NanoToolException("Invalid target denomination: $denomination_to");
        }
        
        $raw = self::den2raw($amount, $denomination_from);
        
        return self::raw2den($raw, $denomination_to);
    }
    
    
    // *
    // *  Account to public key
    // *
    
    public static function account2public(string $account, bool $get_public_key = true)
    {
        if (((strpos($account, 'xrb_1') === 0  ||  
              strpos($account, 'xrb_3') === 0) &&
             strlen($account) == 64) ||
            ((strpos($account, 'nano_1') === 0  ||  
              strpos($account, 'nano_3') === 0) &&
             strlen($account) == 65)
        ) {
            $crop = explode('_', $account);
            $crop = $crop[1];
            
            if (preg_match('/^[13456789abcdefghijkmnopqrstuwxyz]+$/', $crop)) {
                $aux = utils\Uint::fromString(substr($crop, 0, 52))->toUint4()->toArray();
                array_shift($aux);
                $key_uint4  = $aux;
                $hash_uint8 = utils\Uint::fromString(substr($crop, 52, 60))->toUint8()->toArray();
                $key_uint8  = utils\Uint::fromUint4Array($key_uint4)->toUint8();
                
                if (!extension_loaded('blake2')) {
                    $key_hash = new SplFixedArray(64);
                    $b2b = new Blake2b();
                    $ctx = $b2b->init(null, 5);
                    $b2b->update($ctx, $key_uint8, 32);
                    $b2b->finish($ctx, $key_hash);
                    $key_hash = array_reverse(array_slice($key_hash->toArray(), 0, 5));
                } else {
                    $key_uint8 = utils\Bin::arr2bin((array) $key_uint8);
                    $key_hash = blake2($key_uint8, 5, null, true);
                    $key_hash = utils\Bin::bin2arr(strrev($key_hash));
                }
                
                if ($hash_uint8 == $key_hash) {
                    if ($get_public_key) {
                        return utils\Uint::fromUint4Array($key_uint4)->toHexString();
                    } else {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    
    // *
    // *  Public key to account
    // *
    
    public static function public2account(string $public_key): string
    {
        if (strlen($public_key) != 64 || !hex2bin($public_key)) {
            throw new NanoToolException("Invalid public key: $public_key");
        }

        if (!extension_loaded('blake2')) {
            $key = utils\Uint::fromHex($public_key);
            $checksum;
            $hash = new SplFixedArray(64);
            
            $b2b = new Blake2b();
            $ctx = $b2b->init(null, 5);
            $b2b->update($ctx, $key->toUint8(), 32);
            $b2b->finish($ctx, $hash);
            $hash = utils\Uint::fromUint8Array(array_slice($hash->toArray(), 0, 5))->reverse();
            $checksum = $hash->toString();
        } else {
            $key = utils\Uint::fromHex($public_key)->toUint8();
            $key = utils\Bin::arr2bin((array) $key);
            
            $hash = blake2($key, 5, null, true);
            $hash = utils\Bin::bin2arr(strrev($hash));
            $checksum = utils\Uint::fromUint8Array($hash)->toString();
        }
        
        $c_account = utils\Uint::fromHex('0' . $public_key)->toString();
        
        return 'nano_' . $c_account . $checksum;
    }
    
    
    // *
    // *  Private key to public key
    // *
    
    public static function private2public(string $private_key): string
    {
        if (strlen($private_key) != 64 || !hex2bin($private_key)) {
            throw new NanoToolException("Invalid private key: $private_key");
        }
        
        $salt = Salt::instance();
        $private_key = utils\Uint::fromHex($private_key)->toUint8();
        $public_key = $salt::crypto_sign_public_from_secret_key($private_key);
        
        return utils\Uint::fromUint8Array($public_key)->toHexString();
    }
    
    
    // *
    // *  Get random keypair
    // *
    
    public static function keys(bool $get_account = false): array
    {
        $salt = Salt::instance();
        $keys = $salt->crypto_sign_keypair();
        
        $keys[0] = utils\Uint::fromUint8Array(array_slice($keys[0]->toArray(), 0, 32))->toHexString();
        $keys[1] = utils\Uint::fromUint8Array($keys[1])->toHexString();
        
        if ($get_account) {
            $keys[] = self::public2account($keys[1]);
        }
        
        return $keys;
    }
    
    
    // *
    // *  Seed to keypair (Blake2b)
    // *
    
    public static function seed2keys(string $seed, int $index = 0, bool $get_account = false): array
    {
        if (strlen($seed) != 64 || !hex2bin($seed)) {
            throw new NanoToolException("Invalid seed: $seed");
        }
        if ($index < 0 || $index > 4294967295) {
            throw new NanoToolException("Invalid index: $index");
        }
        
        $seed  = utils\Uint::fromHex($seed)->toUint8();
        $index = utils\Uint::fromDec($index)->toUint8()->toArray();
        
        if (count($index) < 4) {
            $missing_bytes = [];
            for ($i = 0; $i < (4 - count($index)); $i++) {
                $missing_bytes[] = 0;
            }
            $index = array_merge($missing_bytes, $index);
        }
        
        $index = utils\Uint::fromUint8Array($index)->toUint8();
        $private_key = new SplFixedArray(64);
        
        $b2b = new Blake2b();
        $ctx = $b2b->init(null, 32);
        $b2b->update($ctx, $seed, 32);
        $b2b->update($ctx, $index, 4);
        $b2b->finish($ctx, $private_key);
        
        $private_key = utils\Uint::fromUint8Array(array_slice($private_key->toArray(), 0, 32))->toHexString();
        $public_key = self::private2public($private_key);
        
        $keys = [$private_key,$public_key];
        
        if ($get_account) {
            $keys[] = self::public2account($public_key);
        }
        
        return $keys;
    }
    
    
    // *
    // *  Mnemonic seed to hexadecimal string (BIP39)
    // *
    
    public static function mnem2hex(array $words): string
    {
        if (count($words) != 12 && count($words) != 24) {
            throw new NanoToolException("Invalid words array count: not 12 or 24");
        }
        
        $bip39 = new BIP39\BIP39EnglishWordList();    
        $bip39_words = $bip39->getWords();
        $mnem_count = count($words);
        $bits = [];
        $hex  = [];
        
        foreach ($words as $index => $value) {
            $word = array_search($value, $bip39_words);
            if ($word === false) {
                throw new NanoToolException("Invalid menmonic word: $value");
            }
            
            $words[$index] = decbin($word);
            $words[$index] = str_split(str_repeat('0', (11 - strlen($words[$index]))) . $words[$index]);
            
            foreach ($words[$index] as $bit) {
                $bits[] = $bit;
            }
        }
        
        for ($i = 0; $i < ceil($mnem_count*2.66); $i++) {
            $hex[] = bindec(implode('', array_slice($bits, $i * 8, 8)));
        }
        
        $hex = utils\Uint::fromUint8Array($hex)->toHexString();
        $hex = substr($hex, 0, ceil($mnem_count*2.66));
        
        return $hex;
    }
    
    
    // *
    // *  Hexadecimal string to mnemonic words (BIP39)
    // *
    
    public static function hex2mnem(string $hex): array
    {
        if ((strlen($hex) != 32 &&
             strlen($hex) != 64) ||
            !hex2bin($hex)
        ) {
            throw new NanoToolException("Invalid hexadecimal string: $hex");
        }
        
        $bip39 = new BIP39\BIP39EnglishWordList();
        $bip39_words = $bip39->getWords();
        $hex_lenght = strlen($hex);
        $bits     = [];
        $mnemonic = [];
        
        $hex = utils\Uint::fromHex($hex)->toUint8();
        $check = hash('sha256', utils\Bin::arr2bin((array) $hex), true);
        $hex  = array_merge((array) $hex, utils\Bin::bin2arr(substr($check, 0, 1)));
        
        foreach ($hex as $byte) {
            $bits_raw = decbin($byte);
            $bits     = array_merge($bits, str_split(str_repeat('0', (8 - strlen($bits_raw))) . $bits_raw));
        }
        
        for ($i = 0; $i < floor($hex_lenght/2.66); $i++) {
            $mnemonic[] = $bip39_words[bindec(implode('', array_slice($bits, $i * 11, 11)))];
        }
        
        return $mnemonic;
    }
    
    
    // *
    // *  Mnemonic words to master seed (BIP39/44)
    // *
    
    public static function mnem2mseed(array $words, string $passphrase = ''): string
    {
        if (count($words) < 1) {
            throw new NanoToolException("Invalid words array count: less than 1");
        }
        
        $bip39 = new BIP39\BIP39EnglishWordList();
        $bip39_words = $bip39->getWords();
        
        foreach ($words as $index => $value) {
            $word = array_search($value, $bip39_words);
            if ($word === false) {
                throw new NanoToolException("Invalid menmonic word: $value");
            }
        }
        
        return strtoupper(
            hash_pbkdf2('sha512', implode(' ', $words), 'mnemonic' . $passphrase, 2048, 128)
        );
    }
    
    
    // *
    // *  Master seed to keypair (BIP39/44)
    // *
    
    public static function mseed2keys(string $mseed, int $index = 0, bool $get_account = false): array
    {
        if (strlen($mseed) != 128 || !hex2bin($mseed)) {
            throw new NanoToolException("Invalid master seed: $mseed");
        }
        if ($index < 0 || $index > 4294967295) {
            throw new NanoToolException("Invalid index: $index");
        }
        
        $path = ["44","165","$index"];
        
        $I     = hash_hmac('sha512', hex2bin($mseed), 'ed25519 seed', true);
        $HDKey = [substr($I, 0, 32),substr($I, 32, 32)];
        
        foreach ($path as $entry) {
            $entry = intval($entry);
            if ($entry >= self::HARDENED) {
                $entry = $entry - self::HARDENED;
            }
            
            $data  = chr(0x00) . $HDKey[0] . hex2bin(dechex(self::HARDENED + (int) $entry));
            $I     = hash_hmac('sha512', $data, $HDKey[1], true);
            $HDKey = [substr($I, 0, 32),substr($I, 32, 32)];
        }
        
        $private_key = strtoupper(bin2hex($HDKey[0]));
        $keys = [$private_key,self::private2public($private_key)];
        
        if ($get_account) {
            $keys[] = self::public2account($keys[1]);
        }
        
        return $keys;
    }
    
    
    // *
    // *  Hash array of hexadecimals
    // *
    
    public static function hashHexs(array $hexs, int $size = 32): string
    {
        if (count($hexs) < 1) {
            throw new NanoToolException("Invalid hexadecimals array count: less than 1");
        }
        if ($size < 1) {
            throw new NanoToolException("Invalid size: $size");
        }
        
        $b2b = new Blake2b();
        
        $ctx  = $b2b->init(null, $size);
        $hash = new SplFixedArray(64);
        
        foreach ($hexs as $index => $value) {
            if (!hex2bin($value)) {
                throw new NanoToolException("Invalid hexadecimal string: $value");
            }
            
            $value = utils\Uint::fromHex($value)->toUint8();
            $b2b->update($ctx, $value, count($value));
        }

        $b2b->finish($ctx, $hash);
        $hash = $hash->toArray();
        $hash = array_slice($hash, 0, $size);
        $hash = utils\Uint::fromUint8Array($hash)->toHexString();
        
        return $hash;
    }
    
    
    // *
    // *  Sign message
    // *
    
    public static function sign(string $msg, string $private_key): string
    {
        if (!hex2bin($msg)) {
            throw new NanoToolException("Invalid message: $msg");
        }
        if (strlen($private_key) != 64 || !hex2bin($private_key)) {
            throw new NanoToolException("Invalid private key: $private_key");
        }
        
        $salt = Salt::instance();
        $private_key = FieldElement::fromArray(utils\Uint::fromHex($private_key)->toUint8());
        $public_key  = Salt::crypto_sign_public_from_secret_key($private_key);
        
        $private_key->setSize(64);
        $private_key->copy($public_key, 32, 32);
        
        $msg = utils\Uint::fromHex($msg)->toUint8();
        $sm  = $salt->crypto_sign($msg, count($msg), $private_key);
        
        $signature = [];
        for ($i = 0; $i < 64; $i++) {
            $signature[$i] = $sm[$i];
        }
        
        return utils\Uint::fromUint8Array($signature)->toHexString();
    }
    
    
    // *
    // *  Validate signature
    // *
    
    public static function validSign(string $msg, string $sig, string $account)
    {
        if (!hex2bin($msg)) {
            throw new NanoToolException("Invalid message: $msg");
        }
        if (strlen($sig) != 128 || !hex2bin($sig)) {
            throw new NanoToolException("Invalid signature: $sig");
        }
        $public_key = self::account2public($account);
        if (!$public_key) {
            throw new NanoToolException("Invalid account: $account");
        }
        
        $sig = utils\Uint::fromHex($sig)->toUint8();
        $msg = utils\Uint::fromHex($msg)->toUint8();
        $public_key  = utils\Uint::fromHex($public_key)->toUint8();
        
        $sm = new SplFixedArray(64 + count($msg));
        $m  = new SplFixedArray(64 + count($msg));
        
        for ($i = 0; $i < 64; $i++) {
            $sm[$i] = $sig[$i];
        }
        for ($i = 0; $i < count($msg); $i++) {
            $sm[$i+64] = $msg[$i];
        }
        
        $open2 = Salt::crypto_sign_open2($m, $sm, count($sm), $public_key);
        
        if ($open2 == null) {
            return false;
        }
        
        $open2 = utils\Uint::fromUint8Array($open2)->toHexString();
        
        return $open2;
    }
    
    
    // *
    // *  Multiplier to difficulty
    // *
    
    public static function mult2diff(string $difficulty, float $multiplier): string
    {
        if (strlen($difficulty) != 16 || !hex2bin($difficulty)) {
            throw new NanoToolException("Invalid difficulty: $difficulty");
        }
        if ($multiplier <= 0) {
            throw new NanoToolException("Invalid multiplier: $multiplier");
        }
        
        $ref = (float) 18446744073709551616;
        $difficulty = hexdec($difficulty);
        
        return dechex(($difficulty - $ref) / $multiplier + $ref);
    }
    
    
    // *
    // *  Difficulty to muliplier
    // *
    
    public static function diff2mult(string $base_difficulty, string $difficulty): float
    {
        if (strlen($base_difficulty) != 16 || !hex2bin($base_difficulty)) {
            throw new NanoToolException("Invalid base difficulty: $base_difficulty");
        }
        if (strlen($difficulty) != 16 || !hex2bin($difficulty)) {
            throw new NanoToolException("Invalid difficulty: $difficulty");
        }

        $ref = (float) 18446744073709551616;
        $base_difficulty = hexdec($base_difficulty);
        $difficulty = hexdec($difficulty);
        
        return (float) ($ref - $base_difficulty) / (float) ($ref - $difficulty);
    }
    
    
    // *
    // *  Generate work
    // *
    
    public static function work(string $hash, string $difficulty): string
    {
        if (strlen($hash) != 64 || !hex2bin($hash)) {
            throw new NanoToolException("Invalid hash: $hash");
        }
        if (strlen($difficulty) != 16 || !hex2bin($difficulty)) {
            throw new NanoToolException("Invalid difficulty: $difficulty");
        }
        
        $hash = utils\Uint::fromHex($hash)->toUint8();
        $difficulty = hex2bin($difficulty);
        
        if (!extension_loaded('blake2')) {
            $b2b = new Blake2b();
            $rng = random_bytes(8);
            $rng = utils\bin2arr($rng);
            
            while (true) {
                $output = new SplFixedArray(64);
                
                $ctx = $b2b->init(null, 8);
                $b2b->update($ctx, $rng, 8);
                $b2b->update($ctx, $hash, 32);
                $b2b->finish($ctx, $output);
                
                $output = $output->toArray();
                $output = array_slice($output, 0, 8);
                $output = array_reverse($output);
                //$output = utils\Uint::fromUint8Array($output)->toHexString();
                
                if (strcasecmp(utils\Bin::arr2bin($output), $difficulty) >= 0) {
                    return utils\Uint::fromUint8Array(array_reverse($rng))->toHexString();
                }

                $rng = $output;
            }
        } else {
            $hash = utils\Bin::arr2bin((array) $hash);
            $rng = random_bytes(8);
            
            while (true) {
                $output = strrev(blake2($rng . $hash, 8, null, true));
                
                if (strcasecmp($output, $difficulty) >= 0) {
                    return utils\Uint::fromUint8Array(array_reverse(utils\bin2arr($rng)))->toHexString();
                }
                
                $rng = $output;
            }
        }
    }
    
    
    // *
    // *  Validate work
    // *
    
    public static function validWork(string $hash, string $difficulty, string $work): bool
    {
        if (strlen($hash) != 64 || !hex2bin($hash)) {
            throw new NanoToolException("Invalid hash: $hash");
        }
        if (strlen($difficulty) != 16 || !hex2bin($difficulty)) {
            throw new NanoToolException("Invalid difficulty: $difficulty");
        }
        if (strlen($work) != 16 || !hex2bin($work)) {
            throw new NanoToolException("Invalid work: $work");
        }
        
        $hash = utils\Uint::fromHex($hash)->toUint8();
        $work = utils\Uint::fromHex($work)->toUint8();
        $work = array_reverse($work->toArray());
        $work = SplFixedArray::fromArray($work);
        
        $res = new SplFixedArray(64);
        
        $blake2b = new Blake2b();
        $ctx = $blake2b->init(null, 8);
        $blake2b->update($ctx, $work, 8);
        $blake2b->update($ctx, $hash, 32);
        $blake2b->finish($ctx, $res);
        
        $res = $res->toArray();
        $res = array_slice($res, 0, 8);
        $res = array_reverse($res);
        $res = utils\Uint::fromUint8Array($res)->toHexString();
        
        if (hexdec($res) >= hexdec($difficulty)) {
            return true;
        }
        
        return false;
    }
}
