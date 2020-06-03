<?php

use php4nano\NanoTools;
use PHPUnit\Framework\TestCase;

class Mnem2hexTest extends TestCase
{
    public function testInvalidInputLength(): void
    {
        $this->expectExceptionObject(new Exception('Words array count is not 12 or 24'));
        NanoTools::mnem2hex(['test']);
    }

    /**
     * @dataProvider data
     * @param array $words
     * @param string $expected
     * @throws Exception
     */
    public function testConvert(array $words, string $expected): void
    {
        $this->assertEquals($expected, NanoTools::mnem2hex($words));
    }

    public function data(): array
    {
        return [
            [
                [
                    'turkey',
                    'fever',
                    'wish',
                    'tray',
                    'remind',
                    'abandon',
                    'announce',
                    'skin',
                    'input',
                    'permit',
                    'mobile',
                    'exclude',
                    'ghost',
                    'album',
                    'floor',
                    'utility',
                    'attack',
                    'oil',
                    'payment',
                    'stumble',
                    'noise',
                    'orbit',
                    'grain',
                    'dash'
                ],
                'EAAAAFF273DB5C0002565474B46639A776180BD65F820E733A86EBD95D37D961'
            ],
        ];
    }
}
