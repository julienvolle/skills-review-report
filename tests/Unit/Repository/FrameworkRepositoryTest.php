<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\Framework;
use App\Repository\FrameworkRepository;
use App\Tests\CustomTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Security;

/**
 * @group unit
 */
class FrameworkRepositoryTest extends CustomTestCase
{
    private ?FrameworkRepository $repository = null;

    public function setUp(): void
    {
        $this->setProphecies([
            ManagerRegistry::class,
            EntityManagerInterface::class,
            ClassMetadata::class,
            Security::class,
            Framework::class,
        ]);

        $this->getProphecy(ManagerRegistry::class)
            ->getManagerForClass(Argument::exact(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(EntityManagerInterface::class));

        $this->getProphecy(EntityManagerInterface::class)
            ->getClassMetadata(Argument::exact(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(ClassMetadata::class));

        $this->getProphecy(ClassMetadata::class)->name = Framework::class;

        $this->repository = new FrameworkRepository(
            $this->getReveal(ManagerRegistry::class),
            $this->getReveal(Security::class)
        );
    }

    public function tearDown(): void
    {
        unset($this->repository);

        parent::tearDown();
    }

    public function testIsUsedWithNoResult(): void
    {
        $this->getProphecy(EntityManagerInterface::class)
            ->createQueryBuilder()
            ->shouldBeCalledOnce()
            ->willThrow(NoResultException::class);

        self::assertFalse($this->repository->isUsed($this->getReveal(Framework::class)));
    }

    public function testIsUsedWithNonUniqueResult(): void
    {
        $this->getProphecy(EntityManagerInterface::class)
            ->createQueryBuilder()
            ->shouldBeCalledOnce()
            ->willThrow(NonUniqueResultException::class);

        self::assertTrue($this->repository->isUsed($this->getReveal(Framework::class)));
    }
}
