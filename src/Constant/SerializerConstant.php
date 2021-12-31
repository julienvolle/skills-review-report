<?php

declare(strict_types=1);

namespace App\Constant;

final class SerializerConstant
{
    public const GROUP_EXPORT           = 'export';
    public const GROUP_EXPORT_FRAMEWORK = 'export_framework';
    public const GROUP_EXPORT_INTERVIEW = 'export_interview';
    public const GROUP_HASH             = 'hash';

    public const GROUPS = [
        self::GROUP_EXPORT,
        self::GROUP_EXPORT_FRAMEWORK,
        self::GROUP_EXPORT_INTERVIEW,
        self::GROUP_HASH,
    ];

    public const FORMAT_EXPORT = 'json';
}
