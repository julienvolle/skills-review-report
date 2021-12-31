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
class EqualsTest extends AbstractTestFrameworkService
{
    /** @dataProvider providerTestEquals */
    public function testEquals($a, $b): void
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
            ->shouldBeCalledTimes(2)
            ->willReturn($a->getName(), $b->getName());

        self::assertSame($a->getName() === $b->getName(), $this->frameworkService->equals($a, $b));
    }

    public function providerTestEquals(): iterable
    {
        yield 'equals'     => [(new Framework())->setName('A'), (new Framework())->setName('A')];
        yield 'not_equals' => [(new Framework())->setName('A'), (new Framework())->setName('B')];
    }
}
