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
class HashTest extends AbstractTestInterviewService
{
    public function testHash(): void
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
            ->shouldBeCalledOnce()
            ->willReturn('data');

        $hash = $this->interviewService->hash(new Interview());
        self::assertIsString($hash);
        self::assertSame($hash, hash('sha256', 'data'));
    }
}
