<?php

declare(strict_types=1);

namespace App\Constant;

final class SecurityConstant
{
    public const ROLE_USER        = 'ROLE_USER';
    public const ROLE_ADMIN       = 'ROLE_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public const ROLES = [
        self::ROLE_USER,
        self::ROLE_ADMIN,
        self::ROLE_SUPER_ADMIN,
    ];

    public const ENCRYPTION_BASE64 = 'base64';
    public const ENCRYPTION_AES256 = 'aes256';

    public const ENCRYPTIONS = [
        self::ENCRYPTION_BASE64,
        self::ENCRYPTION_AES256,
    ];

    public const DEFAULT_CYPHER_AES256 = 'AES-256-CBC';
}
