<?php

declare(strict_types=1);

namespace App\Constant;

final class TranslationConstant
{
    public const LOCALE_ENGLISH = 'en';
    public const LOCALE_FRENCH  = 'fr';

    public const DEFAULT_LOCALE = self::LOCALE_ENGLISH;

    public const LANGUAGES = [
        self::LOCALE_ENGLISH,
        self::LOCALE_FRENCH,
    ];
}
