<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Kernel;
use App\Tests\CustomTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @group functional
 */
class KernelTest extends CustomTestCase
{
    /** @dataProvider providerTestKernel */
    public function testKernel(string $environment, bool $debug): void
    {
        $kernel = new Kernel($environment, $debug);
        $kernel->boot();

        self::assertSame($debug, $kernel->isDebug());
        self::assertSame($environment, $kernel->getEnvironment());
        self::assertSame(__DIR__, $kernel->getProjectDir() . '/tests/Functional');
        self::assertSame($kernel->getProjectDir() . '/var/cache/' . $environment, $kernel->getCacheDir());
        self::assertSame($kernel->getProjectDir() . '/var/log', $kernel->getLogDir());
        self::assertInstanceOf(ContainerInterface::class, $kernel->getContainer());
        self::assertContainsOnlyInstancesOf(BundleInterface::class, $kernel->getBundles());

        $kernel->shutdown();
        unset($kernel);
    }

    public function providerTestKernel(): iterable
    {
        yield 'test' => ['test', true];
        yield 'dev'  => ['dev',  true];
        yield 'prod' => ['prod', false];
    }
}
