<?php

declare(strict_types=1);

namespace App\Tests\Unit\Loader;

use App\Loader\SemverFileLoader;
use App\Tests\CustomTestCase;
use InvalidArgumentException;
use LogicException;
use Prophecy\Argument;
use stdClass;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;

/**
 * @group unit
 */
class SemverFileLoaderTest extends CustomTestCase
{
    public function setUp(): void
    {
        $this->setProphecy(FileLocator::class);
    }

    public function testLoad(): void
    {
        $resource = basename(__FILE__);

        $this->getProphecy(FileLocator::class)
            ->locate(Argument::exact($resource))
            ->shouldBeCalledOnce()
            ->willReturn(__FILE__);

        $loader = new SemverFileLoader($this->getReveal(FileLocator::class));
        self::assertIsString($loader->load($resource));
    }

    public function testLoadFileGetContentsFailure(): void
    {
        $resource = basename(__FILE__);

        $this->getProphecy(FileLocator::class)
            ->locate(Argument::exact($resource))
            ->shouldBeCalledOnce()
            ->willReturn('invalid_path');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf('File "%s" is not found', $resource));

        $loader = new SemverFileLoader($this->getReveal(FileLocator::class));
        $loader->load($resource);
    }

    /** @dataProvider providerTestLoadLocatorException */
    public function testLoadLocatorException(string $exceptionClass): void
    {
        $resource = basename(__FILE__);

        $this->getProphecy(FileLocator::class)
            ->locate(Argument::exact($resource))
            ->shouldBeCalledOnce()
            ->willThrow($exceptionClass);

        $this->expectException($exceptionClass);

        $loader = new SemverFileLoader($this->getReveal(FileLocator::class));
        $loader->load($resource);
    }

    public function providerTestLoadLocatorException(): iterable
    {
        yield 'file_not_found'   => [FileLocatorFileNotFoundException::class];
        yield 'invalid_argument' => [InvalidArgumentException::class];
    }

    public function testSupports(): void
    {
        $loader = new SemverFileLoader(new FileLocator());

        self::assertFalse($loader->supports(null));
        self::assertFalse($loader->supports(123));
        self::assertFalse($loader->supports(true));
        self::assertFalse($loader->supports([]));
        self::assertFalse($loader->supports(new stdClass()));
        self::assertFalse($loader->supports(''));
        self::assertFalse($loader->supports('.env'));

        self::assertTrue($loader->supports('.semver'));
    }
}
