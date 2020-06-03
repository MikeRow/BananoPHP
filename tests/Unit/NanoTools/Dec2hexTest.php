<?php

use php4nano\NanoTools;
use PHPUnit\Framework\TestCase;

class Dec2hexTest extends TestCase
{
    /**
     * @dataProvider data
     * @param string $decString
     * @param string $expected
     * @throws Exception
     */
    public function testConvert(string $decString, string $expected): void
    {
        $this->assertEquals($expected, NanoTools::dec2hex($decString));
    }

    public function data(): array
    {
        return [
            ['2020', '07e4'],
            [
                '115522015506741413552468376787470218603871886200306080305044391135369953419741',
                'ff6724c892fa21ddff6724c892fa21ddff6724c892fa21ddff6724c892fa21dd'
            ],
        ];
    }
}
