<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\ColorService;
use App\Tests\CustomTestCase;
use InvalidArgumentException;

/**
 * @group unit
 */
class ColorServiceTest extends CustomTestCase
{
    public function testGetGradientColor(): void
    {
        $colors = ColorService::getGradientColor(5, '#FFFFFF', '#000000');

        self::assertCount(5, $colors);
        self::assertSame('FFFFFF', $colors[0]);
        self::assertSame('BFBFBF', $colors[1]);
        self::assertSame('7F7F7F', $colors[2]);
        self::assertSame('3F3F3F', $colors[3]);
        self::assertSame('000000', $colors[4]);
    }

    /** @dataProvider providerTestToHexOrDecColor */
    public function testToDecColor($input, $output): void
    {
        self::assertSame($output, ColorService::toDecColor($input));
    }

    /** @dataProvider providerTestToHexOrDecColor */
    public function testToHexColor($output, $input): void
    {
        self::assertSame($output, ColorService::toHexColor($input));
    }

    public function providerTestToHexOrDecColor(): iterable
    {
        yield 'black'   => ['000000', [ 0,   0,   0   ]];
        yield 'red'     => ['FF0000', [ 255, 0,   0   ]];
        yield 'yellow'  => ['FFFF00', [ 255, 255, 0   ]];
        yield 'white'   => ['FFFFFF', [ 255, 255, 255 ]];
        yield 'cyan'    => ['00FFFF', [ 0,   255, 255 ]];
        yield 'blue'    => ['0000FF', [ 0,   0,   255 ]];
        yield 'green'   => ['00FF00', [ 0,   255, 0   ]];
        yield 'purple'  => ['FF00FF', [ 255, 0,   255 ]];
        yield 'custom1' => ['93C47D', [ 147, 196, 125 ]];
        yield 'custom2' => ['464646', [  70,  70,  70 ]];
    }

    /** @dataProvider providerTestInvalidArgument */
    public function testInvalidArgument(string $method, $arguments): void
    {
        $this->expectException(InvalidArgumentException::class);

        ColorService::$method($arguments);
    }

    public function providerTestInvalidArgument(): iterable
    {
        yield 'invalid_hex_color'   => ['toDecColor', 'invalid_hex_color'];
        yield 'invalid_dec_count'   => ['toHexColor', []];
        yield 'invalid_dec_color_1' => ['toHexColor', ['invalid_dec_color', 0, 0]];
        yield 'invalid_dec_color_2' => ['toHexColor', [0, 'invalid_dec_color', 0]];
        yield 'invalid_dec_color_3' => ['toHexColor', [0, 0, 'invalid_dec_color']];
    }
}
