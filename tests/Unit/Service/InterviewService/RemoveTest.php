<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\InterviewService;

use App\Entity\Interview;
use App\Exception\Interview\InterviewRemoveException;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Security;

/**
 * @group unit
 */
class RemoveTest extends AbstractTestInterviewService
{
    public function testRemove(): void
    {
        $interview = new Interview();

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::DELETE),
                Argument::exact($interview)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(EntityManagerInterface::class)
            ->remove(Argument::exact($interview))
            ->shouldBeCalledOnce();

        $this->getProphecy(EntityManagerInterface::class)
            ->flush()
            ->shouldBeCalledOnce();

        $this->interviewService->remove($interview);
    }

    public function testRemoveDenyAccess(): void
    {
        $interview = new Interview();

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::DELETE),
                Argument::exact($interview)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->expectException(InterviewRemoveException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->interviewService->remove($interview);
    }

    public function testRemoveFailure(): void
    {
        $interview = new Interview();

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::DELETE),
                Argument::exact($interview)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(EntityManagerInterface::class)
            ->remove(Argument::exact($interview))
            ->shouldBeCalledOnce();

        $this->getProphecy(EntityManagerInterface::class)
            ->flush()
            ->shouldBeCalledOnce()
            ->willThrow(new ORMException('error_orm_message'));

        $this->expectException(InterviewRemoveException::class);
        $this->expectExceptionMessage('error_orm_message');

        $this->interviewService->remove($interview);
    }
}
