<?php

declare(strict_types=1);

namespace App\Provider;

use App\Security\Encryption\EncryptionInterface;
use LogicException;

class EncryptionProvider
{
    private iterable $encryptionCollection;

    public function __construct(iterable $encryptionCollection)
    {
        $this->encryptionCollection = $encryptionCollection;
    }

    public function getEncryption(string $name): EncryptionInterface
    {
        /** @var EncryptionInterface $encryption */
        foreach ($this->encryptionCollection as $encryption) {
            if ($encryption->support($name)) {
                return $encryption;
            }
        }

        throw new LogicException(\sprintf('Encryption "%s" is not supported', $name));
    }
}
