<?php

namespace mikerow\php4nano\util;

use \SplFixedArray;

class Uint
{
    public $u8;
    public $u4;
    public $u5;
    public $hex;
    public $string;
    
    public function __construct()
    {
        return $this;
    }
    
    protected function clean()
    {
        $this->u8 = $this->u4 = $this->u5 = $this->hex = $this->string = false;
    }
    
    public static function fromHex($hex)
    {
        $ret = new Uint();
        $ret->hex = strtoupper($hex);
        $ret->u8 = $ret->hexU8($hex);
        $ret->u4 = $ret->hexU4($hex);
        return $ret;
    }
    
    public static function fromUint8Array($u8)
    {
        $ret = new Uint();
        $ret->u8 = $u8;
        $ret->hex = $ret->u8Hex($u8);
        return $ret;
    }
    
    public static function fromUint4Array($u4)
    {
        $ret = new Uint();
        $ret->u4 = $u4;
        $ret->hex = $ret->u4Hex($u4);
        $ret->u8 = $ret->u4U8($u4);
        return $ret;
    }
    
    public static function fromString($str)
    {
        $ret = new Uint();
        $ret->u5 = $ret->stringU5($str);
        $ret->string = $str;
        $ret->u4 = $ret->u5U4($ret->u5);
        $ret->u8 = $ret->u4U8($ret->u4);
        return $ret;
    }
    
    public static function fromDec($dec)
    {
        $ret = new Uint();
        $ret->dec = $dec;
        $ret->hex = decToHex($dec);
        $ret->u8 = $ret->hexU8($ret->hex);
        return $ret;
    }
    
    public function toHexString()
    {        
        if($this->hex)
            return $this->hex;
        else
            return $this->u8Hex($this->u8);
    }
    
    public function toString()
    {
        if($this->string)
            return $this->string;
        if($this->u5)
            return $this->u5String($this->u5);
        if($this->u4)
            return $this->u5String($this->u4U5($this->u4));
        if($this->u8)
            return $this->u5String($this->u4U5($this->u8U4($this->u8)));
        return false;
    }
    
    public function toUint8()
    {
        return $this->u8;
    }
    
    public function toUint4()
    {
        if($this->u4)
            return $this->u4;
        if($this->u8)
            return $this->u8U4($this->u8);
        return false;
    }
    
    public function hexU8($hex)
    {
        if(strlen($hex) % 2 != 0)
            $hex = '0' . $hex;
        $arr = new SplFixedArray(strlen($hex) / 2);
        for($i = 0; $i < strlen($hex); $i+=2)
        {
            $arr[$i/2] = base_convert($hex[$i] . $hex[$i+1], 16, 10);
        }
        return $arr;
    }
    
    public function hexU4($hex)
    {
        $arr = new SplFixedArray(strlen($hex));
        for($i = 0; $i < strlen($hex); $i++)
        {
            $arr[$i] = base_convert($hex[$i], 16, 10);
        }
        return $arr;
    }
    
    public function u8Hex($bytes)
    {
        $hex = '';
        foreach($bytes as $byte)
        {
            $aux = base_convert($byte, 10, 16);
            if(strlen($aux) == 1)
                $aux = '0' . $aux;
            $hex .= $aux;
        }
        return strtoupper($hex);
    }
    
    public function u4Hex($bytes)
    {
        $hex = '';
        foreach($bytes as $byte)
            $hex .= base_convert($byte, 10, 16);
        return strtoupper($hex);
    }
    
    public function u8U4($u8)
    {
        $u4 = new SplFixedArray(count($u8) * 2);
        for($i = 0; $i < count($u8); $i++)
        {
            $u4[$i*2] = $u8[$i] / 16 | 0;
            $u4[$i*2 + 1] = $u8[$i] % 16;
        }
        return $u4;
    }
    
    public function u4U8($u4)
    {
        $u8 = new SplFixedArray(count($u4) / 2);
        for($i = 0; $i < count($u8); $i++)
            $u8[$i] = $u4[$i*2] * 16 + $u4[$i*2 + 1];
        return $u8;
    }
    
    public function u4U5($u4)
    {
        $u5 = new SplFixedArray(count($u4) / 5 * 4);
        for($i = 1; $i <= count($u5); $i++)
        {
            $n = $i - 1;
            $m = $i % 4;
            $z = $n + (($i - $m) / 4);
            $right = $u4[$z] << $m;
            if((count($u5) - $i) % 4 == 0)
                $left = $u4[$z - 1] << 4;
            else
                $left = $u4[$z + 1] >> (4 - $m);
            $u5[$n] = ($left + $right) % 32;
        }
        return $u5;
    }
    
    public function u5U4($u5)
    {
        $u4 = new SplFixedArray(count($u5) / 4 * 5);
        for($i = 1; $i <= count($u4); $i++)
        {
            $n = $i - 1;
            $m = $i % 5;
            $z = $n - (($i - $m) / 5);
            if($i > 1)
                $right = $u5[$z - 1] << (5 - $m);
            else $right = 0;
            $left = $u5[$z] >> $m;
            $u4[$n] = ($left + $right) % 16;
        }
        return $u4;
    }
    
    public function stringU5($str)
    {
        $letters = '13456789abcdefghijkmnopqrstuwxyz';
        $len = strlen($str);
        $arr = str_split($str);
        $u5 = new SplFixedArray($len);
        for($i = 0; $i < $len; $i++)
            $u5[$i] = strpos($letters, $arr[$i]);
        return $u5;
    }
    
    public function u5String($u5)
    {
        $letters = str_split('13456789abcdefghijkmnopqrstuwxyz');
        $str = "";
        for($i = 0; $i < count($u5); $i++)
            $str .= $letters[$u5[$i]];
        return $str;
    }
    
    public function dec2hex($str, $bytes = null)
    {
        $dec = str_split($hex);
        $sum = [];
        $hex = [];
        $i = $s = 0;
        while(count($dec))
        {
            $s = 1 * array_shift($dec);
            for($i = 0; $s || $i < count($sum); $i++)
            {
                $s += ($sum[$i] || 0) * 10;
                $sum[$i] = $s % 16;
                $s = ($s - $sum[$i]) / 16;
            }
        }
        while(count($sum))
        {
            $hex[] = base_convert(array_shift($sum), 10, 16);
        }
        
        $hex = implode('', $hex);

        if(strlen($hex) % 2 != 0)
            $hex = "0" . $hex;

        if($bytes > strlen($hex) / 2)
        {
            $diff = $bytes - strlen($hex) / 2;
            for($i = 0; $i < $diff; $i++)
                $hex = "00" . $hex;
        }
        return $hex;
    }
    
    public function reverse()
    {
        if($this->u8)
        {
            $len = count($this->u8);
            for($i = 0; $i != $len - 1 - $i; $i++)
            {
                $aux = $this->u8[$i];
                $this->u8[$i] = $this->u8[$len - 1 - $i];
                $this->u8[$len - 1 - $i] = $aux;
            }
        }
        return self::fromUint8Array($this->toUint8());
    }
    
    public static function expandSize($size, $uint)
    {
        if(get_class($uint) != 'Uint')
            return false;
        if(count($uint->toUint8()) < $size)
        {
            $u8 = $uint->toUint8();
            $new = new SplFixedArray($size);
            $diff = $size - count($u8);
            for($i = 0; $i < $size; $i++)
            {
                if($i < $diff)
                    $new[$i] = 0;
                else
                    $new[$i] = $u8[$i - $diff];
            }
            return self::fromUint8Array($new);
        }
        return $uint;
    }
}
