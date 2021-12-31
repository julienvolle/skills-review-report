<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\InterviewService;

use App\Entity\Interview;
use App\Entity\User;
use App\Exception\Interview\InterviewReplaceException;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @group unit
 */
class ReplaceTest extends AbstractTestInterviewService
{
    public function testReplace(): void
    {
        $interviewToDelete = new Interview();
        $interviewToCreate = new Interview();

        // #1 remove()
        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::DELETE),
                Argument::exact($interviewToDelete)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(EntityManagerInterface::class)
            ->remove(Argument::exact($interviewToDelete))
            ->shouldBeCalledOnce();

        // #2 save()
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
            ->persist(Argument::exact($interviewToCreate))
            ->shouldBeCalledOnce();

        $this->getProphecy(EntityManagerInterface::class)
            ->flush()
            ->shouldBeCalledTimes(2); // remove & save

        $this->interviewService->replace($interviewToDelete, $interviewToCreate);
    }

    public function testReplaceDenyAccess(): void
    {
        $interviewToDelete = new Interview();

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::DELETE),
                Argument::exact($interviewToDelete)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->expectException(InterviewReplaceException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->interviewService->replace($interviewToDelete, new Interview());
    }
}
