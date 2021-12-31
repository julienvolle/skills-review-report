<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\Framework;
use App\Entity\UserFramework;
use App\Repository\UserFrameworkRepository;
use App\Tests\CustomTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @group unit
 */
class UserFrameworkRepositoryTest extends CustomTestCase
{
    private ?UserFrameworkRepository $repository = null;

    public function setUp(): void
    {
        $this->setProphecies([
            ManagerRegistry::class,
            EntityManagerInterface::class,
            ClassMetadata::class,
            Framework::class,
        ]);

        $this->getProphecy(ManagerRegistry::class)
            ->getManagerForClass(Argument::exact(UserFramework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(EntityManagerInterface::class));

        $this->getProphecy(EntityManagerInterface::class)
            ->getClassMetadata(Argument::exact(UserFramework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(ClassMetadata::class));

        $this->getProphecy(ClassMetadata::class)->name = UserFramework::class;

        $this->repository = new UserFrameworkRepository($this->getReveal(ManagerRegistry::class));
    }

    public function tearDown(): void
    {
        unset($this->repository);

        parent::tearDown();
    }

    public function testFindByUserAndFramework(): void
    {
        $this->getProphecy(Framework::class)
            ->getId()
            ->shouldBeCalledOnce()
            ->willReturn(null);

        self::assertNull($this->repository->findByUserAndFramework(
            $this->getReveal(Framework::class),
            $this->createMock(UserInterface::class)
        ));
    }
}
