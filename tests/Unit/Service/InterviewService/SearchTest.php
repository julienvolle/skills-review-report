<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\InterviewService;

use App\Entity\Interview;
use App\Exception\Interview\InterviewSearchException;
use App\Repository\InterviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Prophecy\Argument;
use Symfony\Component\Uid\Uuid;

/**
 * @group unit
 */
class SearchTest extends AbstractTestInterviewService
{
    public function testSearch(): void
    {
        $interview = (new Interview())->setGuid((string) Uuid::v4());

        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Interview::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(InterviewRepository::class));

        $this->getProphecy(InterviewRepository::class)
            ->findOneByGuid(Argument::exact($interview->getGuid()))
            ->shouldBeCalledOnce()
            ->willReturn($interview);

        self::assertInstanceOf(Interview::class, $this->interviewService->search($interview->getGuid()));
    }

    public function testSearchNotFound(): void
    {
        $guid = (string) Uuid::v4();

        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Interview::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(InterviewRepository::class));

        $this->getProphecy(InterviewRepository::class)
            ->findOneByGuid(Argument::exact($guid))
            ->shouldBeCalledOnce()
            ->willReturn(null);

        self::assertNull($this->interviewService->search($guid));
    }

    public function testSearchNonUniqueResult(): void
    {
        $guid = (string) Uuid::v4();

        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Interview::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(InterviewRepository::class));

        $this->getProphecy(InterviewRepository::class)
            ->findOneByGuid(Argument::exact($guid))
            ->shouldBeCalledOnce()
            ->willThrow(NonUniqueResultException::class);

        $this->expectException(InterviewSearchException::class);

        $this->interviewService->search($guid);
    }
}
