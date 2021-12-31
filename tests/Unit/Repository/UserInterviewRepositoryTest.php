<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\Interview;
use App\Entity\UserInterview;
use App\Repository\UserInterviewRepository;
use App\Tests\CustomTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @group unit
 */
class UserInterviewRepositoryTest extends CustomTestCase
{
    private ?UserInterviewRepository $repository = null;

    public function setUp(): void
    {
        $this->setProphecies([
            ManagerRegistry::class,
            EntityManagerInterface::class,
            ClassMetadata::class,
            Interview::class,
        ]);

        $this->getProphecy(ManagerRegistry::class)
            ->getManagerForClass(Argument::exact(UserInterview::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(EntityManagerInterface::class));

        $this->getProphecy(EntityManagerInterface::class)
            ->getClassMetadata(Argument::exact(UserInterview::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(ClassMetadata::class));

        $this->getProphecy(ClassMetadata::class)->name = UserInterview::class;

        $this->repository = new UserInterviewRepository($this->getReveal(ManagerRegistry::class));
    }

    public function tearDown(): void
    {
        unset($this->repository);

        parent::tearDown();
    }

    public function testFindByUserAndInterview(): void
    {
        $this->getProphecy(Interview::class)
            ->getId()
            ->shouldBeCalledOnce()
            ->willReturn(null);

        self::assertNull($this->repository->findByUserAndInterview(
            $this->getReveal(Interview::class),
            $this->createMock(UserInterface::class)
        ));
    }
}
