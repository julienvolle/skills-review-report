<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FrameworkService;

use App\Constant\SerializerConstant;
use App\Entity\Framework;
use App\Exception\Framework\FrameworkExportException;
use App\Model\Export\FrameworkExport;
use App\Security\Voter\AbstractVoter;
use App\Service\SemverService;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @group unit
 */
class ExportTest extends AbstractTestFrameworkService
{
    public function testExport(): void
    {
        $framework = new Framework();

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::EXPORT),
                Argument::exact($framework)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(SemverService::class)
            ->getVersion()
            ->shouldBeCalledOnce()
            ->willReturn('version');

        $this->getProphecy(SerializerInterface::class)
            ->serialize(
                Argument::type(FrameworkExport::class),
                Argument::exact(SerializerConstant::FORMAT_EXPORT),
                Argument::that(function ($argument) {
                    return (
                        is_array($argument)
                        && !empty($argument['groups'])
                        && in_array(SerializerConstant::GROUP_EXPORT, $argument['groups'], true)
                        && in_array(SerializerConstant::GROUP_EXPORT_FRAMEWORK, $argument['groups'], true)
                    );
                })
            )
            ->shouldBeCalledOnce()
            ->willReturn('data');

        self::assertSame('data', $this->frameworkService->export($framework));
    }

    public function testExportDenyAccess(): void
    {
        $framework = new Framework();

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::EXPORT),
                Argument::exact($framework)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->expectException(FrameworkExportException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->frameworkService->export($framework);
    }
}
