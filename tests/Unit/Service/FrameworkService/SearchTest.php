<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FrameworkService;

use App\Entity\Framework;
use App\Exception\Framework\FrameworkSearchException;
use App\Repository\FrameworkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Prophecy\Argument;
use Symfony\Component\Uid\Uuid;

/**
 * @group unit
 */
class SearchTest extends AbstractTestFrameworkService
{
    public function testSearch(): void
    {
        $framework = (new Framework())->setGuid((string) Uuid::v4());

        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(FrameworkRepository::class));

        $this->getProphecy(FrameworkRepository::class)
            ->findOneByGuid(Argument::exact($framework->getGuid()))
            ->shouldBeCalledOnce()
            ->willReturn($framework);

        self::assertInstanceOf(Framework::class, $this->frameworkService->search($framework->getGuid()));
    }

    public function testSearchNotFound(): void
    {
        $guid = (string) Uuid::v4();

        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(FrameworkRepository::class));

        $this->getProphecy(FrameworkRepository::class)
            ->findOneByGuid(Argument::exact($guid))
            ->shouldBeCalledOnce()
            ->willReturn(null);

        self::assertNull($this->frameworkService->search($guid));
    }

    public function testSearchNonUniqueResult(): void
    {
        $guid = (string) Uuid::v4();

        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(FrameworkRepository::class));

        $this->getProphecy(FrameworkRepository::class)
            ->findOneByGuid(Argument::exact($guid))
            ->shouldBeCalledOnce()
            ->willThrow(NonUniqueResultException::class);

        $this->expectException(FrameworkSearchException::class);

        $this->frameworkService->search($guid);
    }
}
