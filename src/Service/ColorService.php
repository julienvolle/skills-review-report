<?php

declare(strict_types=1);

namespace App\Service;

use App\Constant\ColorConstant;
use InvalidArgumentException;

class ColorService
{
    /**
     * @param int    $step          Gradient step number
     * @param string $hexColorStart Hexadecimal color to start
     * @param string $hexColorEnd   Hexadecimal color to end
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    public static function getGradientColor(
        int $step = 5,
        string $hexColorStart = ColorConstant::WHITE,
        string $hexColorEnd = ColorConstant::BLACK
    ): array {
        $step = \max(($step - 2), 0);

        $start = self::toDecColor($hexColorStart);
        $end = self::toDecColor($hexColorEnd);

        $addR = \round(\abs($start[0] - $end[0]) / ($step + 1)) * ($start[0] > $end[0] ? -1 : 1);
        $addG = \round(\abs($start[1] - $end[1]) / ($step + 1)) * ($start[1] > $end[1] ? -1 : 1);
        $addB = \round(\abs($start[2] - $end[2]) / ($step + 1)) * ($start[2] > $end[2] ? -1 : 1);

        $colors = [self::toHexColor($start)];
        for ($i = 0; $i < $step; $i++) {
            $start[0] += $addR;
            $start[1] += $addG;
            $start[2] += $addB;
            $colors[] = self::toHexColor($start);
        }
        $colors[] = self::toHexColor($end);

        return $colors;
    }

    /**
     * Convert "000000" to [0,0,0]
     *
     * @param string $hexColor
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    public static function toDecColor(string $hexColor): array
    {
        $matches = [];
        \preg_match('/^(#?)([A-Z0-9]{6})$/i', \strtoupper($hexColor), $matches);
        $hexColor = $matches[2] ?? null;
        if (!$hexColor) {
            throw new InvalidArgumentException(\sprintf('Invalid color %s', $hexColor));
        }

        return [
            \hexdec(\substr($hexColor, 0, 2)), // R
            \hexdec(\substr($hexColor, 2, 2)), // G
            \hexdec(\substr($hexColor, 4, 2)), // B
        ];
    }

    /**
     * Convert [0,0,0] to "000000"
     *
     * @param array $decColor
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public static function toHexColor(array $decColor): string
    {
        $pattern = '/^(\d{1,3})$/i';
        if (
            \count($decColor) !== 3
            || !\preg_match($pattern, (string) $decColor[0])
            || !\preg_match($pattern, (string) $decColor[1])
            || !\preg_match($pattern, (string) $decColor[2])
        ) {
            throw new InvalidArgumentException(\sprintf('Invalid color [%s]', \implode(',', $decColor)));
        }

        return \strtoupper(self::toHex($decColor[0]) . self::toHex($decColor[1]) . self::toHex($decColor[2]));
    }

    /**
     * @param int|float $dec
     *
     * @return string
     */
    private static function toHex($dec): string
    {
        $hex = \dechex($dec);

        return (\strlen($hex) === 1 ? \str_repeat($hex, 2) : $hex);
    }
}
