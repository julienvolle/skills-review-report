<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\InterviewService;

use App\Entity\Interview;
use App\Entity\User;
use App\Exception\Interview\InterviewSaveException;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Exception\ORMException;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @group unit
 */
class SaveTest extends AbstractTestInterviewService
{
    public function testSave(): void
    {
        $interview = new Interview();

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::CREATE),
                Argument::exact(Interview::class)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(Security::class)
            ->getUser()
            ->shouldBeCalledOnce()
            ->willReturn(new User());

        $this->getProphecy(EventDispatcherInterface::class)
            ->dispatch(Argument::type(PrePersistEventArgs::class))
            ->shouldBeCalledOnce()
            ->willReturn(Argument::type(PrePersistEventArgs::class));

        $this->getProphecy(EntityManagerInterface::class)
            ->persist(Argument::exact($interview))
            ->shouldBeCalledOnce();

        $this->getProphecy(EntityManagerInterface::class)
            ->flush()
            ->shouldBeCalledOnce();

        $this->interviewService->save($interview);
    }

    public function testSaveDenyAccessToCreate(): void
    {
        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::CREATE),
                Argument::exact(Interview::class)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->expectException(InterviewSaveException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->interviewService->save(new Interview());
    }

    public function testSaveDenyAccessToUpdate(): void
    {
        $interview = $this->getMockBuilder(Interview::class)->getMock();
        $interview->method('getId')->willReturn(1);

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::UPDATE),
                Argument::exact($interview)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->expectException(InterviewSaveException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->interviewService->save($interview);
    }

    public function testSaveFailure(): void
    {
        $interview = $this->getMockBuilder(Interview::class)->getMock();
        $interview->method('getId')->willReturn(1);

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::UPDATE),
                Argument::exact($interview)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(EventDispatcherInterface::class)
            ->dispatch(Argument::type(PrePersistEventArgs::class))
            ->shouldBeCalledOnce()
            ->willReturn(Argument::type(PrePersistEventArgs::class));

        $this->getProphecy(EntityManagerInterface::class)
            ->persist(Argument::exact($interview))
            ->shouldBeCalledOnce();

        $this->getProphecy(EntityManagerInterface::class)
            ->flush()
            ->willThrow(new ORMException('error_orm_message'));

        $this->expectException(InterviewSaveException::class);
        $this->expectExceptionMessage('error_orm_message');

        $this->interviewService->save($interview);
    }
}
