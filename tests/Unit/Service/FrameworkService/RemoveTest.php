<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FrameworkService;

use App\Entity\Framework;
use App\Exception\Framework\FrameworkRemoveException;
use App\Repository\FrameworkRepository;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group unit
 */
class RemoveTest extends AbstractTestFrameworkService
{
    public function testRemove(): void
    {
        $framework = new Framework();

        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(FrameworkRepository::class));

        $this->getProphecy(FrameworkRepository::class)
            ->isUsed(Argument::exact($framework))
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::DELETE),
                Argument::exact($framework)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(EntityManagerInterface::class)
            ->remove(Argument::exact($framework))
            ->shouldBeCalledOnce();

        $this->getProphecy(EntityManagerInterface::class)
            ->flush()
            ->shouldBeCalledOnce();

        $this->frameworkService->remove($framework);
    }

    public function testRemoveThenIsUsed(): void
    {
        $framework = new Framework();

        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(FrameworkRepository::class));

        $this->getProphecy(FrameworkRepository::class)
            ->isUsed(Argument::exact($framework))
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(TranslatorInterface::class)
            ->trans(
                Argument::exact('exception.framework.delete.is_used'),
                Argument::type('array'),
                Argument::exact('errors')
            )
            ->shouldBeCalledOnce()
            ->willReturn('error_message_translated');

        $this->expectException(FrameworkRemoveException::class);
        $this->expectExceptionMessage('error_message_translated');

        $this->frameworkService->remove($framework);
    }

    public function testRemoveDenyAccess(): void
    {
        $framework = new Framework();

        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(FrameworkRepository::class));

        $this->getProphecy(FrameworkRepository::class)
            ->isUsed(Argument::exact($framework))
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::DELETE),
                Argument::exact($framework)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->expectException(FrameworkRemoveException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->frameworkService->remove($framework);
    }

    public function testRemoveFailure(): void
    {
        $framework = new Framework();

        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(FrameworkRepository::class));

        $this->getProphecy(FrameworkRepository::class)
            ->isUsed(Argument::exact($framework))
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::DELETE),
                Argument::exact($framework)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(EntityManagerInterface::class)
            ->remove(Argument::exact($framework))
            ->shouldBeCalledOnce();

        $this->getProphecy(EntityManagerInterface::class)
            ->flush()
            ->willThrow(new ORMException('error_orm_message'));

        $this->expectException(FrameworkRemoveException::class);
        $this->expectExceptionMessage('error_orm_message');

        $this->frameworkService->remove($framework);
    }
}
