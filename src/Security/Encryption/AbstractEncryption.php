<?php

declare(strict_types=1);

namespace App\Security\Encryption;

abstract class AbstractEncryption implements EncryptionInterface
{
    public const TAG_HASH_ALGO = 'crc32';

    abstract public function name(): string;

    final public function support(string $name): bool
    {
        return $name === $this->name();
    }

    final protected function isEncrypted(string $value): bool
    {
        return !empty($value) && $value !== $this->unpack($value);
    }

    final protected function pack(string $value): string
    {
        return $this->tag() . $value;
    }

    final protected function unpack(string $value): string
    {
        return \explode($this->tag(), $value)[1] ?? $value;
    }

    private function tag(): string
    {
        return \hash(self::TAG_HASH_ALGO, $this->name()) . '::';
    }
}
