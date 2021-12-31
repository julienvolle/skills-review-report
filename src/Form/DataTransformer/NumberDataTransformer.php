<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class NumberDataTransformer implements DataTransformerInterface
{
    public function transform($value): string
    {
        return \strval($value);
    }

    public function reverseTransform($value): float
    {
        return \floatval($value);
    }
}
