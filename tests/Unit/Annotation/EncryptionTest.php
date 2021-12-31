<?php

declare(strict_types=1);

namespace App\Tests\Unit\Annotation;

use App\Annotation\Encryption;
use App\Constant\SecurityConstant;
use App\Tests\CustomTestCase;

/**
 * @group unit
 */
class EncryptionTest extends CustomTestCase
{
    public function testAnnotation(): void
    {
        self::assertSame(SecurityConstant::ENCRYPTION_BASE64, (new Encryption())->name);
        self::assertSame('test', (new Encryption(['name' => 'test']))->name);

        self::assertNull((new Encryption())->maxLength);
        self::assertSame(255, (new Encryption(['maxLength' => 255]))->maxLength);
    }
}
