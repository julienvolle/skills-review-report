<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;

abstract class AbstractException extends Exception
{
    public function __construct(?string $message = null, array $context = [], ?Throwable $previous = null)
    {
        $this->message = $message ?? '';

        foreach ($context as $key => $value) {
            if (!\is_object($value) && !\is_array($value)) {
                $this->message = \str_replace('{' . $key . '}', $value, $this->message);
            }
        }

        parent::__construct($this->getMessage(), $this->getCode(), $previous);
    }
}
