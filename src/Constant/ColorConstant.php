<?php

declare(strict_types=1);

namespace App\Constant;

/**
 * Check SASS mapping with /assets/styles/_color.scss
 */
final class ColorConstant
{
    public const PINK       = '#EA5297';
    public const GREY       = '#5A5A5A';
    public const GREY_LIGHT = '#CCCCCC';
    public const YELLOW     = '#F4C617';
    public const CYAN       = '#52ACD2';
    public const ORANGE     = '#EE7925';
    public const RED        = '#EC655D';
    public const WHITE      = '#FFFFFF';
    public const BLACK      = '#000000';

    public const COLORS = [
        'pink'       => self::PINK,
        'grey'       => self::GREY,
        'grey_light' => self::GREY_LIGHT,
        'yellow'     => self::YELLOW,
        'cyan'       => self::CYAN,
        'orange'     => self::ORANGE,
        'red'        => self::RED,
        'white'      => self::WHITE,
        'black'      => self::BLACK,
    ];
}
