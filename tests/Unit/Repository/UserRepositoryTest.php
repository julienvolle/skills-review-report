<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\CustomTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @group unit
 */
class UserRepositoryTest extends CustomTestCase
{
    private ?UserRepository $repository = null;

    public function setUp(): void
    {
        $this->setProphecies([
            ManagerRegistry::class,
            EntityManagerInterface::class,
            ClassMetadata::class,
            User::class,
            PasswordAuthenticatedUserInterface::class,
        ]);

        $this->getProphecy(ManagerRegistry::class)
            ->getManagerForClass(Argument::exact(User::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(EntityManagerInterface::class));

        $this->getProphecy(EntityManagerInterface::class)
            ->getClassMetadata(Argument::exact(User::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(ClassMetadata::class));

        $this->repository = new UserRepository($this->getReveal(ManagerRegistry::class));
    }

    public function tearDown(): void
    {
        unset($this->repository);

        parent::tearDown();
    }

    public function testUpgradePassword(): void
    {
        $password = 'hashPassword';

        $this->getProphecy(User::class)
            ->setPassword(Argument::exact($password))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(User::class));

        $this->getProphecy(EntityManagerInterface::class)
            ->persist(Argument::exact($this->getReveal(User::class)))
            ->shouldBeCalledOnce();

        $this->getProphecy(EntityManagerInterface::class)
            ->flush()
            ->shouldBeCalledOnce();

        $this->repository->upgradePassword($this->getReveal(User::class), $password);

        $this->expectException(UnsupportedUserException::class);

        $this->repository->upgradePassword(
            $this->getReveal(PasswordAuthenticatedUserInterface::class),
            $password
        );
    }
}
