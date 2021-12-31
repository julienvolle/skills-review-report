<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\InterviewService;

use App\Constant\SerializerConstant;
use App\Entity\Interview;
use App\Exception\Interview\InterviewExportException;
use App\Model\Export\InterviewExport;
use App\Security\Voter\AbstractVoter;
use App\Service\SemverService;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @group unit
 */
class ExportTest extends AbstractTestInterviewService
{
    public function testExport(): void
    {
        $interview = new Interview();

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::EXPORT),
                Argument::exact($interview)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(SemverService::class)
            ->getVersion()
            ->shouldBeCalledOnce()
            ->willReturn('version');

        $this->getProphecy(SerializerInterface::class)
            ->serialize(
                Argument::type(InterviewExport::class),
                Argument::exact(SerializerConstant::FORMAT_EXPORT),
                Argument::that(function ($argument) {
                    return (
                        is_array($argument)
                        && !empty($argument['groups'])
                        && in_array(SerializerConstant::GROUP_EXPORT, $argument['groups'], true)
                        && in_array(SerializerConstant::GROUP_EXPORT_FRAMEWORK, $argument['groups'], true)
                        && in_array(SerializerConstant::GROUP_EXPORT_INTERVIEW, $argument['groups'], true)
                    );
                })
            )
            ->shouldBeCalledOnce()
            ->willReturn('data');

        self::assertSame('data', $this->interviewService->export($interview));
    }

    public function testExportDenyAccess(): void
    {
        $interview = new Interview();

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::EXPORT),
                Argument::exact($interview)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->expectException(InterviewExportException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->interviewService->export($interview);
    }
}
