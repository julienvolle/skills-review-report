<?php

declare(strict_types=1);

namespace App\Tests\Unit\Exception;

use App\Exception\AbstractException;
use App\Tests\CustomTestCase;
use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group unit
 */
class AbstractExceptionTest extends CustomTestCase
{
    public function testConstructor(): void
    {
        $errorMessage = '[{error_code}] Error message: {error_message}';
        $errorContext = [
            'error_message' => 'BAD_REQUEST',
            'error_code'    => Response::HTTP_BAD_REQUEST,
        ];
        $previousException = new Exception($errorContext['error_message'], $errorContext['error_code']);

        $parameters = [$errorMessage, $errorContext, $previousException];
        $exception = new class (...$parameters) extends AbstractException {
        };

        self::assertSame(
            '[' . $errorContext['error_code'] . '] Error message: ' . $errorContext['error_message'],
            $exception->getMessage()
        );
        self::assertSame($errorContext['error_message'], $exception->getPrevious()->getMessage());
        self::assertSame($errorContext['error_code'], $exception->getPrevious()->getCode());

        $exception = new class () extends AbstractException {
        };

        self::assertSame('', $exception->getMessage());
        self::assertNull($exception->getPrevious());
    }
}
