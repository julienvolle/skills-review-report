<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\InterviewService;

use App\Constant\SerializerConstant;
use App\Entity\Interview;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @group unit
 */
class EqualsTest extends AbstractTestInterviewService
{
    /** @dataProvider providerTestEquals */
    public function testEquals($a, $b): void
    {
        $this->getProphecy(SerializerInterface::class)
            ->serialize(
                Argument::type(Interview::class),
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
            ->willReturn($a->getTitle(), $b->getTitle());

        self::assertSame($a->getTitle() === $b->getTitle(), $this->interviewService->equals($a, $b));
    }

    public function providerTestEquals(): iterable
    {
        yield 'equals'     => [(new Interview())->setTitle('A'), (new Interview())->setTitle('A')];
        yield 'not_equals' => [(new Interview())->setTitle('A'), (new Interview())->setTitle('B')];
    }
}
