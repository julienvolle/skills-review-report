<?php

declare(strict_types=1);

namespace App\Tests\Unit\Provider;

use App\Constant\SecurityConstant;
use App\Provider\EncryptionProvider;
use App\Security\Encryption\Aes256Encryption;
use App\Security\Encryption\Base64Encryption;
use App\Tests\CustomTestCase;
use LogicException;

/**
 * @group unit
 */
class EncryptionProviderTest extends CustomTestCase
{
    public function testGetEncryption(): void
    {
        $provider = new EncryptionProvider([
            $base64 = new Base64Encryption(),
            $aes256 = new Aes256Encryption(),
        ]);

        self::assertSame($base64, $provider->getEncryption(SecurityConstant::ENCRYPTION_BASE64));
        self::assertSame($aes256, $provider->getEncryption(SecurityConstant::ENCRYPTION_AES256));

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Encryption "unknown" is not supported');
        $provider->getEncryption('unknown');
    }
}
