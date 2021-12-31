<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Constant\CacheConstant;
use App\Exception\Semver\SemverCacheException;
use App\Exception\Semver\SemverFileContentsException;
use App\Exception\Semver\SemverFileLoaderException;
use App\Loader\SemverFileLoader;
use App\Model\Semver;
use App\Service\SemverService;
use App\Tests\CustomTestCase;
use Exception;
use Prophecy\Argument;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group unit
 */
class SemverServiceTest extends CustomTestCase
{
    private ?SemverService $semverService = null;

    public function setUp(): void
    {
        $this->setProphecies([
            SemverFileLoader::class,
            CacheItemPoolInterface::class,
            CacheItemInterface::class,
            SerializerInterface::class,
            TranslatorInterface::class,
            Semver::class,
        ]);

        $this->semverService = new SemverService(
            $this->getReveal(SemverFileLoader::class),
            $this->getReveal(CacheItemPoolInterface::class),
            $this->getReveal(SerializerInterface::class),
            $this->getReveal(TranslatorInterface::class)
        );
    }

    public function tearDown(): void
    {
        unset($this->semverService);

        parent::tearDown();
    }

    public function testGetVersion(): void
    {
        $this->getProphecy(CacheItemPoolInterface::class)
            ->getItem(Argument::exact(CacheConstant::APP_VERSION_KEY))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(CacheItemInterface::class));

        $this->getProphecy(SemverFileLoader::class)
            ->load(Argument::type('string'))
            ->shouldBeCalledOnce()
            ->willReturn('string');

        $this->getProphecy(CacheItemInterface::class)
            ->isHit()
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->getProphecy(SerializerInterface::class)
            ->deserialize(
                Argument::type('string'),
                Argument::exact(Semver::class),
                Argument::exact('json')
            )
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Semver::class));

        $this->getProphecy(Semver::class)
            ->__toString()
            ->shouldBeCalledOnce()
            ->willReturn('version');

        $this->getProphecy(CacheItemInterface::class)
            ->set(Argument::exact('version'))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(CacheItemInterface::class));

        $this->getProphecy(CacheItemInterface::class)
            ->expiresAfter(Argument::exact(CacheConstant::APP_VERSION_TTL))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(CacheItemInterface::class));

        $this->getProphecy(CacheItemPoolInterface::class)
            ->save(Argument::exact($this->getReveal(CacheItemInterface::class)))
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(CacheItemInterface::class)
            ->get()
            ->shouldBeCalledOnce()
            ->willReturn('version');

        self::assertSame('version', $this->semverService->getVersion());
    }

    public function testGetVersionFromCache(): void
    {
        $this->getProphecy(CacheItemPoolInterface::class)
            ->getItem(Argument::exact(CacheConstant::APP_VERSION_KEY))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(CacheItemInterface::class));

        $this->getProphecy(CacheItemInterface::class)
            ->isHit()
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(CacheItemInterface::class)
            ->get()
            ->shouldBeCalledOnce()
            ->willReturn('version');

        self::assertSame('version', $this->semverService->getVersion());
    }

    public function testCacheException(): void
    {
        $this->getProphecy(CacheItemPoolInterface::class)
            ->getItem(Argument::exact(CacheConstant::APP_VERSION_KEY))
            ->shouldBeCalledOnce()
            ->willThrow(new class ('error_cache_message') extends Exception implements InvalidArgumentException {
            });

        $this->expectException(SemverCacheException::class);
        $this->expectExceptionMessage('error_cache_message');

        $this->semverService->getVersion();
    }

    public function testFileLoaderException(): void
    {
        $this->getProphecy(CacheItemPoolInterface::class)
            ->getItem(Argument::exact(CacheConstant::APP_VERSION_KEY))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(CacheItemInterface::class));

        $this->getProphecy(CacheItemInterface::class)
            ->isHit()
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->getProphecy(SemverFileLoader::class)
            ->load(Argument::type('string'))
            ->shouldBeCalledOnce()
            ->willThrow(new class ('error_loader_message') extends Exception {
            });

        $this->expectException(SemverFileLoaderException::class);
        $this->expectExceptionMessage('error_loader_message');

        $this->semverService->getVersion();
    }

    public function testFileContentsException(): void
    {
        $this->getProphecy(CacheItemPoolInterface::class)
            ->getItem(Argument::exact(CacheConstant::APP_VERSION_KEY))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(CacheItemInterface::class));

        $this->getProphecy(CacheItemInterface::class)
            ->isHit()
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->getProphecy(SemverFileLoader::class)
            ->load(Argument::type('string'))
            ->shouldBeCalledOnce()
            ->willReturn('string');

        $this->getProphecy(SerializerInterface::class)
            ->deserialize(
                Argument::type('string'),
                Argument::exact(Semver::class),
                Argument::exact('json')
            )
            ->shouldBeCalledOnce()
            ->willReturn('no_semver_instance');

        $this->getProphecy(TranslatorInterface::class)
            ->trans(
                Argument::exact('exception.semver.invalid'),
                Argument::type('array'),
                Argument::exact('errors')
            )
            ->shouldBeCalledOnce()
            ->willReturn('error_message_translated');

        $this->expectException(SemverFileContentsException::class);
        $this->expectExceptionMessage('error_message_translated');

        $this->semverService->getVersion();
    }
}
