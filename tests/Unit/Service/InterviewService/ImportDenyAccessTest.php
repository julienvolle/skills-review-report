<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\InterviewService;

use App\Entity\Framework;
use App\Entity\Interview;
use App\Exception\Interview\InterviewImportException;
use App\Repository\InterviewRepository;
use App\Security\Voter\AbstractVoter;
use App\Service\FrameworkService;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\Uuid;

/**
 * @group unit
 */
class ImportDenyAccessTest extends AbstractTestInterviewService
{
    public function testImportDenyAccessAll(): void
    {
        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::IMPORT),
                Argument::exact(Interview::class)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false); // isGranted() for ALL = KO

        $this->expectException(InterviewImportException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->interviewService->import(new Interview());
    }

    public function testImportDenyAccessOne(): void
    {
        $framework = new Framework();
        $interview = (new Interview())->setGuid((string) Uuid::v4())->setFramework($framework);

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::IMPORT),
                Argument::exact(Interview::class)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true); // isGranted() for ALL = OK

        $this->getProphecy(FrameworkService::class)
            ->import(
                Argument::exact($framework),
                Argument::exact(false)
            )
            ->shouldBeCalledOnce()
            ->willReturn($framework);

        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Interview::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(InterviewRepository::class));

        $this->getProphecy(InterviewRepository::class)
            ->findOneByGuid(Argument::exact($interview->getGuid()))
            ->shouldBeCalledOnce()
            ->willReturn($interview);

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::IMPORT),
                Argument::exact($interview)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false); // isGranted() for ONE = KO

        $this->expectException(InterviewImportException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->interviewService->import($interview);
    }
}
