<?php

declare(strict_types=1);

namespace App\Security\Encryption;

use App\Constant\SecurityConstant;

class Aes256Encryption extends Base64Encryption
{
    public const HMAC_ALGO = 'sha3-512';
    public const HMAC_ALGO_LENGTH = 64;

    private string $passphrase;
    private string $cypher;

    public function __construct(string $passphrase = '', string $cypher = SecurityConstant::DEFAULT_CYPHER_AES256)
    {
        $this->passphrase = $passphrase;
        $this->cypher = $cypher;
    }

    public function name(): string
    {
        return SecurityConstant::ENCRYPTION_AES256;
    }

    public function encrypt(string $value): string
    {
        if ($this->isEncrypted($value)) {
            return $value;
        }
        $ivLength = \openssl_cipher_iv_length($this->cypher);
        $iv = \openssl_random_pseudo_bytes($ivLength);
        $encrypted = \openssl_encrypt($value, $this->cypher, $this->passphrase, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            // @codeCoverageIgnoreStart
            return $value; // cannot mock qualified native PHP function "openssl_encrypt()" to return false
            // @codeCoverageIgnoreEnd
        }
        $hmac = \hash_hmac(self::HMAC_ALGO, $encrypted, $this->passphrase, true);
        $parent = parent::encrypt($iv . $hmac . $encrypted);

        return $encrypted === $parent ? $value : $parent;
    }

    public function decrypt(string $value): string
    {
        if ($value === $parent = parent::decrypt($value)) {
            return $value;
        }
        $ivLength = \openssl_cipher_iv_length($this->cypher);
        $iv = \substr($parent, 0, $ivLength);
        $hmac = \substr($parent, $ivLength, self::HMAC_ALGO_LENGTH);
        $encrypted = \substr($parent, $ivLength + self::HMAC_ALGO_LENGTH);
        $decrypted = \openssl_decrypt($encrypted, $this->cypher, $this->passphrase, OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false) {
            // @codeCoverageIgnoreStart
            return $value; // cannot mock qualified native PHP function "openssl_decrypt()" to return false
            // @codeCoverageIgnoreEnd
        }
        $hash = \hash_hmac(self::HMAC_ALGO, $encrypted, $this->passphrase, true);

        return \hash_equals($hmac, $hash) ? $decrypted : $value;
    }
}
