<?php

declare(strict_types=1);

namespace App\Security\Encryption;

use App\Constant\SecurityConstant;

class Base64Encryption extends AbstractEncryption
{
    public function name(): string
    {
        return SecurityConstant::ENCRYPTION_BASE64;
    }

    public function encrypt(string $value): string
    {
        return $this->isEncrypted($value) ? $value : $this->pack(\base64_encode($value));
    }

    public function decrypt(string $value): string
    {
        return $this->isEncrypted($value) ? \base64_decode($this->unpack($value), true) : $value;
    }
}
