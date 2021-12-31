<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FrameworkService;

use App\Constant\SerializerConstant;
use App\Entity\Framework;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @group unit
 */
class HashTest extends AbstractTestFrameworkService
{
    public function testHash(): void
    {
        $this->getProphecy(SerializerInterface::class)
            ->serialize(
                Argument::type(Framework::class),
                Argument::exact(SerializerConstant::FORMAT_EXPORT),
                Argument::that(function ($argument) {
                    return (
                        is_array($argument)
                        && !empty($argument['groups'])
                        && in_array(SerializerConstant::GROUP_HASH, $argument['groups'], true)
                    );
                })
            )
            ->shouldBeCalledOnce()
            ->willReturn('data');

        $hash = $this->frameworkService->hash(new Framework());
        self::assertIsString($hash);
        self::assertSame($hash, hash('sha256', 'data'));
    }
}
