<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\InterviewService;

use App\Constant\SerializerConstant;
use App\Entity\Framework;
use App\Entity\Interview;
use App\Exception\Interview\InterviewImportException;
use App\Repository\InterviewRepository;
use App\Security\Voter\AbstractVoter;
use App\Service\FrameworkService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @group unit
 */
class ImportExistingForceDenyAccessTest extends AbstractTestInterviewService
{
    public function testImportExistingForceThenIsUsed(): void
    {
        $framework = new Framework();
        $interview = $this->getMockBuilder(Interview::class)->getMock();
        $interview->method('getId')->willReturn(null);
        $interview->method('getGuid')->willReturn((string) Uuid::v4());
        $interview->method('getUserInterviews')->willReturn(new ArrayCollection());
        $interview->method('getFramework')->willReturn($framework);

        // #1 isGranted() for ALL
        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::IMPORT),
                Argument::exact(Interview::class)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        // #2 import() Framework
        $this->getProphecy(FrameworkService::class)
            ->import(
                Argument::exact($framework),
                Argument::exact(true) // Also force import framework
            )
            ->shouldBeCalledOnce()
            ->willReturn($framework);

        // #3 search()
        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Interview::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(InterviewRepository::class));

        $this->getProphecy(InterviewRepository::class)
            ->findOneByGuid(Argument::exact($interview->getGuid()))
            ->shouldBeCalledOnce()
            ->willReturn($interview);

        // #4 isGranted() for ONE
        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::IMPORT),
                Argument::exact($interview)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        // #5 equals()
        $this->getProphecy(SerializerInterface::class)
            ->serialize(
                Argument::type(Interview::class),
                Argument::exact(SerializerConstant::FORMAT_EXPORT),
                Argument::type('array')
            )
            ->shouldBeCalledTimes(2)
            ->willReturn('hash_1', 'hash_2'); // Is not equals !

        // #6 replace()->remove()

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::DELETE),
                Argument::exact($interview)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->expectException(InterviewImportException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->interviewService->import($interview, true); // Force
    }
}
