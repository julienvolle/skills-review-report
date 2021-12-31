<?php

declare(strict_types=1);

namespace App\Annotation;

use App\Constant\SecurityConstant;

/**
 * @Annotation
 */
class Encryption
{
    public ?string $name = null;
    public ?int $maxLength = null;

    public function __construct(array $values = [])
    {
        $this->name = $values['name'] ?? SecurityConstant::ENCRYPTION_BASE64;
        $this->maxLength = $values['maxLength'] ?? null;
    }
}
