<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Encryption;

use App\Constant\SecurityConstant;
use App\Security\Encryption\AbstractEncryption;
use App\Security\Encryption\Base64Encryption;
use App\Tests\CustomTestCase;

/**
 * @group unit
 */
class Base64EncryptionTest extends CustomTestCase
{
    private ?string $tag = null;

    public function setUp(): void
    {
        $this->tag = \hash(AbstractEncryption::TAG_HASH_ALGO, (new Base64Encryption())->name()) . '::';
    }

    public function testName(): void
    {
        self::assertSame(SecurityConstant::ENCRYPTION_BASE64, (new Base64Encryption())->name());
    }

    /** @dataProvider providerTestEncryption */
    public function testEncryption(string $input): void
    {
        $output = base64_encode($input);
        self::assertSame($this->tag . $output, (new Base64Encryption())->encrypt($input));
        self::assertSame($this->tag . $input, (new Base64Encryption())->encrypt($this->tag . $input));
        self::assertSame($input, (new Base64Encryption())->decrypt($this->tag . $output));
        self::assertSame($output, (new Base64Encryption())->decrypt($output));
    }

    public function providerTestEncryption(): iterable
    {
        yield 'empty'       => [     ''];
        yield '1_char'      => [    'a'];
        yield '2_chars'     => [   'ab'];
        yield '3_chars'     => [  'abc'];
        yield '4_chars'     => [ 'test'];
        yield 'number'      => [  '123'];
        yield 'float'       => [ '1.23'];
        yield 'null'        => [ 'null'];
        yield 'boolean_1'   => [ 'true'];
        yield 'boolean_2'   => ['false'];
        yield 'boolean_3'   => [    '1'];
        yield 'boolean_4'   => [    '0'];
        yield 'sp_char_1'   => [    '%'];
        yield 'sp_char_2'   => [    '#'];
        yield 'sp_char_3'   => [    '@'];
        yield 'firstname'   => [ 'John'];
        yield 'lastname'    => [  'Doe'];
    }
}
