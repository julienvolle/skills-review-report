<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FrameworkService;

use App\Entity\Framework;
use App\Entity\User;
use App\Exception\Framework\FrameworkReplaceException;
use App\Repository\FrameworkRepository;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Security;

/**
 * @group unit
 */
class ReplaceTest extends AbstractTestFrameworkService
{
    public function testReplace(): void
    {
        $frameworkToDelete = new Framework();
        $frameworkToCreate = new Framework();

        // #1 remove()
        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(FrameworkRepository::class));

        $this->getProphecy(FrameworkRepository::class)
            ->isUsed(Argument::exact($frameworkToDelete))
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::DELETE),
                Argument::exact($frameworkToDelete)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(EntityManagerInterface::class)
            ->remove(Argument::exact($frameworkToDelete))
            ->shouldBeCalledOnce();

        // #2 save()
        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::CREATE),
                Argument::exact(Framework::class)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(Security::class)
            ->getUser()
            ->shouldBeCalledOnce()
            ->willReturn(new User());

        $this->getProphecy(EntityManagerInterface::class)
            ->persist(Argument::exact($frameworkToCreate))
            ->shouldBeCalledOnce();

        $this->getProphecy(EntityManagerInterface::class)
            ->flush()
            ->shouldBeCalledTimes(2); // remove & save

        $this->frameworkService->replace($frameworkToDelete, $frameworkToCreate);
    }

    public function testReplaceDenyAccess(): void
    {
        $frameworkToDelete = new Framework();

        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(FrameworkRepository::class));

        $this->getProphecy(FrameworkRepository::class)
            ->isUsed(Argument::exact($frameworkToDelete))
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::DELETE),
                Argument::exact($frameworkToDelete)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->expectException(FrameworkReplaceException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->frameworkService->replace($frameworkToDelete, new Framework());
    }
}
