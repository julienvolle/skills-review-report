<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Encryption;

use App\Constant\SecurityConstant;
use App\Security\Encryption\AbstractEncryption;
use App\Security\Encryption\Aes256Encryption;
use App\Tests\CustomTestCase;

/**
 * @group unit
 */
class Aes256EncryptionTest extends CustomTestCase
{
    private const REG_EXP_BASE64 = '?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{4}|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{2}={2}';

    private ?string $tag = null;

    public function setUp(): void
    {
        $this->tag = \hash(AbstractEncryption::TAG_HASH_ALGO, (new Aes256Encryption())->name()) . '::';
    }

    public function testName(): void
    {
        self::assertSame(SecurityConstant::ENCRYPTION_AES256, (new Aes256Encryption())->name());
    }

    /** @dataProvider providerTestEncryption */
    public function testEncryption(string $input): void
    {
        $pattern = \sprintf('/^(%s)(%s)$/', $this->tag, self::REG_EXP_BASE64);
        self::assertMatchesRegularExpression($pattern, $output = (new Aes256Encryption())->encrypt($input));
        self::assertSame($this->tag . $input, (new Aes256Encryption())->encrypt($this->tag . $input));
        self::assertSame($input, (new Aes256Encryption())->decrypt($output));
        self::assertSame($input, (new Aes256Encryption())->decrypt($input));
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
