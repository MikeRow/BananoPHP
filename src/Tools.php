<?php

    namespace php4nano;

    require_once __DIR__ . '/../lib/Salt/autoload.php';
    require_once __DIR__ . '/../lib/Util.php';
    
    use \Exception as Exception;
    use \Util as Util;
    use \Uint as Uint;
    use \SplFixedArray as SplFixedArray;
    use \Blake2b as Blake2b;
    use \Salt as Salt;
    use \FieldElement as FieldElement;
    use \hexToDec as hexToDec;
    use \decToHex as decToHex;

    class Tools
    {
        // #
        // ## Constants
        // #
        
        const RAWS =
        [
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
        
        
        // #
        // ## Hexadecimal string to decimal string
        // #
        
        public static function hex2dec(string $string): string
        {
            if (!ctype_xdigit($string)) {
                throw new Exception("Invalid hexadecimal string: $string");
            }
            
            $dec = hexToDec($string);
            
            if ($dec == '') {
                return '0';
            } else {
                return $dec;
            }
        }
        
        
        // #
        // ## Decimal string to hexadecimal string
        // #
        
        public static function dec2hex(string $string): string
        {
            if (!ctype_digit($string)) {
                throw new Exception("Invalid decimal string: $string");
            }
            
            $hex = decToHex($string);
            
            if ($hex == '') {
                return '00';
            } else {
                return $hex;
            }
        }
        
        
        // #
        // ## Integer array to binary string
        // #
        
        public static function arr2bin(array $array): string
        {
            foreach ($array as $value) {
                if (!ctype_digit((string) $value)) {
                    throw new Exception("Invalid integer array value: $value");
                }
                if ($value > 255) {
                    throw new Exception("Invalid integer array value: $value");
                }
            }
            
            return implode(array_map('chr', $array));
        }
        
        
        // #
        // ## Binary string to integer array
        // #
        
        public static function bin2arr(string $string): array
        {
            return array_map('ord', str_split($string));
        }
        
        
        // #
        // ## Denomination to raw
        // #
        
        public static function den2raw($amount, string $denomination): string
        {
            if (!array_key_exists($denomination, self::RAWS)) {
                throw new Exception("Invalid denomination: $denomination");
            }
            
            $raw_to_denomination = self::RAWS[$denomination];
            
            if ($amount == 0) {
                return '0';
            }
            
            if (strpos($amount, '.')) {
                $dot_pos             = strpos($amount, '.');
                $number_len          = strlen($amount) - 1;
                $raw_to_denomination = substr($raw_to_denomination, 0, -($number_len - $dot_pos));
            }
            
            $amount = str_replace('.', '', $amount) . str_replace('1', '', $raw_to_denomination);
            
            // Remove useless zeros from left
            
            while (substr($amount, 0, 1) == '0') {
                $amount = substr($amount, 1);
            }
            
            return $amount;
        }
    
    
        // #
        // ## Raw to denomination
        // #
        
        public static function raw2den(string $amount, string $denomination): string
        {
            if (!array_key_exists($denomination, self::RAWS)) {
                throw new Exception("Invalid denomination: $denomination");
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
        
        
        // #
        // ## Denomination to denomination
        // #
        
        public static function den2den($amount, string $denomination_from, string $denomination_to): string
        {
            if (!array_key_exists($denomination_from, self::RAWS)) {
                throw new Exception("Invalid source denomination: $denomination_from");
            }
            if (!array_key_exists($denomination_to, self::RAWS)) {
                throw new Exception("Invalid target denomination: $denomination_to");
            }
            
            $raw = self::den2raw($amount, $denomination_from);
            
            return self::raw2den($raw, $denomination_to);
        }
        
        
        // #
        // ## Account to public key
        // #
        
        public static function account2public(string $account, bool $get_public_key = true)
        {
            if (
               (strpos($account, 'xrb_1') === 0   ||
                strpos($account, 'xrb_3') === 0   ||
                strpos($account, 'nano_1') === 0  ||
                strpos($account, 'nano_3') === 0)
            && (strlen($account) == 64 || strlen($account) == 65)) {
                
                $crop = explode('_', $account);
                $crop = $crop[1];
                
                if (preg_match('/^[13456789abcdefghijkmnopqrstuwxyz]+$/', $crop)) {
                    $aux = Uint::fromString(substr($crop, 0, 52))->toUint4()->toArray();
                    array_shift($aux);
                    $key_uint4  = $aux;
                    $hash_uint8 = Uint::fromString(substr($crop, 52, 60))->toUint8()->toArray();
                    $key_uint8  = Uint::fromUint4Array($key_uint4)->toUint8();
                    
                    if (!extension_loaded('blake2')) {
                        $key_hash = new SplFixedArray(64);
                        $b2b = new Blake2b();
                        $ctx = $b2b->init(null, 5);
                        $b2b->update($ctx, $key_uint8, 32);
                        $b2b->finish($ctx, $key_hash);
                        $key_hash = array_reverse(array_slice($key_hash->toArray(), 0, 5));
                    } else {
                        $key_uint8 = self::arr2bin((array) $key_uint8);
                        $key_hash = blake2($key_uint8, 5, null, true);
                        $key_hash = self::bin2arr(strrev($key_hash));
                    }
                    
                    if ($hash_uint8 == $key_hash) {
                        if ($get_public_key) {
                            return Uint::fromUint4Array($key_uint4)->toHexString();
                        } else {
                            return true;
                        }
                    }
                }
            }
            
            return false;
        }
        
        
        // #
        // ## Public key to account
        // #
        
        public static function public2account(string $public_key): string
        {
            if (strlen($public_key) != 64 || !hex2bin($public_key)) {
                throw new Exception("Invalid public key: $public_key");
            }

            if (!extension_loaded('blake2')) {
                $key = Uint::fromHex($public_key);
                $checksum;
                $hash = new SplFixedArray(64);
                
                $b2b = new Blake2b();
                $ctx = $b2b->init(null, 5);
                $b2b->update($ctx, $key->toUint8(), 32);
                $b2b->finish($ctx, $hash);
                
                $hash     = Uint::fromUint8Array(array_slice($hash->toArray(), 0, 5))->reverse();
                $checksum = $hash->toString();
            } else {
                $key = Uint::fromHex($public_key)->toUint8();
                $key = self::arr2bin((array) $key);
                $hash = blake2($key, 5, null, true);
                $hash = self::bin2arr(strrev($hash));
                $checksum = Uint::fromUint8Array($hash)->toString();
            }
            
            $c_account = Uint::fromHex('0' . $public_key)->toString();
            
            return 'nano_' . $c_account . $checksum;
        }
        
        
        // #
        // ## Private key to public key
        // #
        
        public static function private2public(string $private_key): string
        {
            if (strlen($private_key) != 64 || !hex2bin($private_key)) {
                throw new Exception("Invalid private key: $private_key");
            }
            
            $salt = Salt::instance();
            $private_key = Uint::fromHex($private_key)->toUint8();
            $public_key = $salt::crypto_sign_public_from_secret_key($private_key);
            
            return Uint::fromUint8Array($public_key)->toHexString();
        }
        
        
        // #
        // ## Get random keypair
        // #
        
        public static function keys(bool $get_account = false): array
        {
            $salt = Salt::instance();
            $keys = $salt->crypto_sign_keypair();
            
            $keys[0] = Uint::fromUint8Array(array_slice($keys[0]->toArray(), 0, 32))->toHexString();
            $keys[1] = Uint::fromUint8Array($keys[1])->toHexString();
            
            if ($get_account) {
                $keys[] = self::public2account($keys[1]);
            }
            
            return $keys;
        }
        
        
        // #
        // ## BLAKE2: seed to keypair
        // #
        
        public static function seed2keys(string $seed, int $index = 0, bool $get_account = false): array
        {
            if (strlen($seed) != 64 || !hex2bin($seed)) {
                throw new Exception("Invalid seed: $seed");
            }
            if ($index < 0 || $index > 4294967295) {
                throw new Exception("Invalid index: $index");
            }
            
            $seed  = Uint::fromHex($seed)->toUint8();
            $index = Uint::fromDec($index)->toUint8()->toArray();
            
            if (count($index) < 4) {
                $missing_bytes = [];
                for ($i = 0; $i < (4 - count($index)); $i++) {
                    $missing_bytes[] = 0;
                }
                $index = array_merge($missing_bytes, $index);
            }
            
            $index = Uint::fromUint8Array($index)->toUint8();
            $private_key    = new SplFixedArray(64);
            
            $b2b = new Blake2b();
            $ctx = $b2b->init(null, 32);
            $b2b->update($ctx, $seed, 32);
            $b2b->update($ctx, $index, 4);
            $b2b->finish($ctx, $private_key);
            
            $private_key = Uint::fromUint8Array(array_slice($private_key->toArray(), 0, 32))->toHexString();
            $public_key = self::private2public($private_key);
            
            $keys = [$private_key,$public_key];
            
            if ($get_account) {
                $keys[] = self::public2account($public_key);
            }
            
            return $keys;
        }
        
        
        // #
        // ## BIP39: mnemonic seed to hexadecimal seed
        // #
        
        public static function mnem2hex(array $words): string
        {
            if (!is_array($words) || count($words) != 24) {
                throw new Exception("Words array count is not 24");
            }
            
            $bip39_words = json_decode(file_get_contents(__DIR__ . '/../lib/BIP/BIP39_en.json'), true);
            $bits = [];
            $hex  = [];
            
            foreach ($words as $index => $value) {
                $word = array_search($value, $bip39_words);
                if ($word === false) {
                    throw new Exception("Invalid menmonic word: $value");
                }
                
                $words[$index] = decbin($word) ;
                $words[$index] = str_split(str_repeat('0', (11 - strlen($words[$index]))) . $words[$index]);
                
                foreach ($words[$index] as $bit) {
                    $bits[] = $bit;
                }
            }
            
            for ($i = 0; $i < 32; $i++) {
                $hex[] = bindec(implode('', array_slice($bits, $i * 8, 8)));
            }
            
            $hex = Uint::fromUint8Array($hex)->toHexString();
            $hex = substr($hex, 0, 64);
            
            return $hex;
        }
        
        
        // #
        // ## BIP39: hexadecimal seed to mnemonic seed
        // #
        
        public static function hex2mnem(string $hex): array
        {
            if (strlen($hex) != 64 || !hex2bin($hex)) {
                throw new Exception("Invalid seed: $hex");
            }
            
            $bip39_words = json_decode(file_get_contents(__DIR__ . '/../lib/BIP/BIP39_en.json'), true);
            $bits     = [];
            $mnemonic = [];
            
            $hex  = Uint::fromHex($hex)->toUint8();
            $check = hash('sha256', self::arr2bin((array) $hex), true);
            $hex  = array_merge((array) $hex, self::bin2arr(substr($check, 0, 1)));
            
            foreach ($hex as $byte) {
                $bits_raw = decbin($byte);
                $bits     = array_merge($bits, str_split(str_repeat('0', (8 - strlen($bits_raw))) . $bits_raw));
            }
            
            for ($i = 0; $i < 24; $i++) {
                $mnemonic[] = $bip39_words[bindec(implode('', array_slice($bits, $i * 11, 11)))];
            }
            
            return $mnemonic;
        }
        
        
        // #
        // ## BIP39/44: mnemonic words to master seed
        // #
        
        public static function mnem2mseed(array $words, string $passphrase = ''): string
        {
            if (!is_array($words) || count($words) != 24) {
                throw new Exception("Words array count is not 24");
            }
            
            return strtoupper(hash_pbkdf2('sha512', implode(' ', $words), 'mnemonic' . $passphrase, 2048, 128)) ;
        }
        
        
        // #
        // ## BIP39/44: master seed to keypair
        // #
        
        public static function mseed2keys(string $seed, int $index = 0, bool $get_account = false): array
        {
            if (strlen($seed) != 128 || !hex2bin($seed)) {
                throw new Exception("Invalid seed: $seed");
            }
            if ($index < 0 || $index > 4294967295) {
                throw new Exception("Invalid index: $index");
            }
            
            $path = ["44","165","$index"];
            
            $I     = hash_hmac('sha512', hex2bin($seed), 'ed25519 seed', true);
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
            
            $private_key   = strtoupper(bin2hex($HDKey[0]));
            $keys = [$private_key,self::private2public($private_key)];
            
            if ($get_account) {
                $keys[] = self::public2account($keys[1]);
            }
            
            return $keys;
        }
        
        
        // #
        // ## Get block ID
        // #
        
        public static function getBlockId(array $hexs): string
        {
            if (count($hexs) != 6) {
                throw new Exception("Hexadecimals array count is not 6");
            }
            
            $b2b = new Blake2b();
            
            $ctx  = $b2b->init(null, 32);
            $hash = new SplFixedArray(64);
            
            foreach ($hexs as $index => $value) {
                if (!hex2bin($value)) {
                    throw new Exception("Invalid hexadecimal string: $value");
                }
                
                $value = Uint::fromHex($value)->toUint8();
                $b2b->update($ctx, $value, count($value));
            }

            $b2b->finish($ctx, $hash);
            $hash = $hash->toArray();
            $hash = array_slice($hash, 0, 32);
            $hash = Uint::fromUint8Array($hash)->toHexString();
            
            return $hash;
        }
        
        
        // #
        // ## Sign message
        // #
        
        public static function signMsg(string $private_key, string $msg): string
        {
            if (strlen($private_key) != 64 || !hex2bin($private_key)) {
                throw new Exception("Invalid private key: $private_key");
            }
            if (!hex2bin($msg)) {
                throw new Exception("Invalid block ID: $msg");
            }
            
            $salt = Salt::instance();
            $private_key  = FieldElement::fromArray(Uint::fromHex($private_key)->toUint8());
            $public_key   = Salt::crypto_sign_public_from_secret_key($private_key);
            
            $private_key->setSize(64);
            $private_key->copy($public_key, 32, 32);
            
            $msg = Uint::fromHex($msg)->toUint8();
            $sm  = $salt->crypto_sign($msg, count($msg), $private_key);
            
            $signature = [];
            for ($i = 0; $i < 64; $i++) {
                $signature[$i] = $sm[$i];
            }
            
            return Uint::fromUint8Array($signature)->toHexString();
        }
        
        
        // #
        // ## Validate signature
        // #
        
        public static function validSign(string $msg, string $sig, string $account)
        {
            if (strlen($msg) != 64 || !hex2bin($msg)) {
                throw new Exception("Invalid block ID: $msg");
            }
            if (strlen($sig) != 128 || !hex2bin($sig)) {
                throw new Exception("Invalid signature: $sig");
            }
            $public_key = self::account2public($account);
            if (!$public_key) {
                throw new Exception("Invalid account: $account");
            }
            
            $sig = Uint::fromHex($sig)->toUint8();
            $msg = Uint::fromHex($msg)->toUint8();
            $public_key  = Uint::fromHex($public_key)->toUint8();
            
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
            
            $open2 = Uint::fromUint8Array($open2)->toHexString();
            
            return $open2;
        }
        
        
        // #
        // ## Multiply difficulty
        // #
        
        public static function multDiff(string $difficulty, float $multiplier): string
        {
            if (strlen($difficulty) != 16 || !hex2bin($difficulty)) {
                throw new Exception("Invalid difficulty: $difficulty");
            }
            
            return dechex(ceil(hexdec($difficulty) * $multiplier));
        }
        
        
        // #
        // ## Generate work
        // #
        
        public static function getWork(string $hash, string $difficulty): string
        {
            if (strlen($hash) != 64 || !hex2bin($hash)) {
                throw new Exception("Invalid block ID: $hash");
            }
            if (strlen($difficulty) != 16 || !hex2bin($difficulty)) {
                throw new Exception("Invalid difficulty: $difficulty");
            }
            
            $hash = Uint::fromHex($hash)->toUint8();
            
            if (!extension_loaded('blake2')) {
                $difficulty = hexdec($difficulty);
                
                $b2b = new Blake2b();
                
                while (true) {
                    $rng = [];
                    for ($i = 0; $i < 8; $i++) {
                        $rng[] = mt_rand(0, 255);
                    }
                    
                    $output = new SplFixedArray(64);
                    
                    $ctx = $b2b->init(null, 8);
                    $b2b->update($ctx, $rng, 8);
                    $b2b->update($ctx, $hash, 32);
                    $b2b->finish($ctx, $output);
                    
                    $output = $output->toArray();
                    $output = array_slice($output, 0, 8);
                    $output = array_reverse($output);
                    $output = Uint::fromUint8Array($output)->toHexString();
                    
                    if (hexdec($output) >= $difficulty) {
                        return Uint::fromUint8Array(array_reverse($rng))->toHexString();
                    }
                }
            } else {
                $hash = self::arr2bin((array) $hash);
                $difficulty = hex2bin($difficulty);
                
                while (true) {
                    $rng = random_bytes(8);
                    
                    $output = blake2($rng . $hash, 8, null, true);
                    $output = strrev($output);
                    
                    if (strcasecmp($output, $difficulty) >= 0) {
                        return Uint::fromUint8Array(array_reverse(self::bin2arr($rng)))->toHexString();
                    }
                }
            }
        }
        
        
        // #
        // ## Validate work
        // #
        
        public static function validWork(string $hash, string $work, string $difficulty): bool
        {
            if (strlen($hash) != 64 || !hex2bin($hash)) {
                throw new Exception("Invalid block ID: $hash");
            }
            if (strlen($work) != 16 || !hex2bin($work)) {
                throw new Exception("Invalid work: $work");
            }
            if (strlen($difficulty) != 16 || !hex2bin($difficulty)) {
                throw new Exception("Invalid difficulty: $difficulty");
            }
            
            $hash = Uint::fromHex($hash)->toUint8();
            $work = Uint::fromHex($work)->toUint8();
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
            $res = Uint::fromUint8Array($res)->toHexString();
            
            if (self::hex2dec($res) >= self::hex2dec($difficulty)) {
                return true;
            }
            
            return false;
        }
    }
