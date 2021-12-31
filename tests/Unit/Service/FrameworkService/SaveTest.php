<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FrameworkService;

use App\Entity\Framework;
use App\Entity\User;
use App\Exception\Framework\FrameworkSaveException;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Security;

/**
 * @group unit
 */
class SaveTest extends AbstractTestFrameworkService
{
    public function testSave(): void
    {
        $framework = new Framework();

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
            ->persist(Argument::exact($framework))
            ->shouldBeCalledOnce();

        $this->getProphecy(EntityManagerInterface::class)
            ->flush()
            ->shouldBeCalledOnce();

        $this->frameworkService->save($framework);
    }

    public function testSaveDenyAccessToCreate(): void
    {
        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::CREATE),
                Argument::exact(Framework::class)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->expectException(FrameworkSaveException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->frameworkService->save(new Framework());
    }

    public function testSaveDenyAccessToUpdate(): void
    {
        $framework = $this->getMockBuilder(Framework::class)->getMock();
        $framework->method('getId')->willReturn(1);

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::UPDATE),
                Argument::exact($framework)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->expectException(FrameworkSaveException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->frameworkService->save($framework);
    }

    public function testSaveFailure(): void
    {
        $framework = $this->getMockBuilder(Framework::class)->getMock();
        $framework->method('getId')->willReturn(1);

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::UPDATE),
                Argument::exact($framework)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(EntityManagerInterface::class)
            ->persist(Argument::exact($framework))
            ->shouldBeCalledOnce();

        $this->getProphecy(EntityManagerInterface::class)
            ->flush()
            ->willThrow(new ORMException('error_orm_message'));

        $this->expectException(FrameworkSaveException::class);
        $this->expectExceptionMessage('error_orm_message');

        $this->frameworkService->save($framework);
    }
}
