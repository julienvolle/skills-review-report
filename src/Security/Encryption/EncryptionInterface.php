<?php

declare(strict_types=1);

namespace App\Security\Encryption;

interface EncryptionInterface
{
    public function support(string $name): bool;
    public function encrypt(string $value): string;
    public function decrypt(string $value): string;
}
