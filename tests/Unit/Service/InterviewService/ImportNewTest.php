<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\InterviewService;

use App\Entity\Framework;
use App\Entity\Interview;
use App\Entity\User;
use App\Repository\InterviewRepository;
use App\Security\Voter\AbstractVoter;
use App\Service\FrameworkService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group unit
 */
class ImportNewTest extends AbstractTestInterviewService
{
    public function testImportNew(): void
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
                Argument::exact(false)
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
            ->willReturn(null);

        // #4 save()
        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::CREATE),
                Argument::exact(Interview::class)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(Security::class)
            ->getUser()
            ->shouldBeCalledOnce()
            ->willReturn(new User());

        $this->getProphecy(EventDispatcherInterface::class)
            ->dispatch(Argument::type(PrePersistEventArgs::class))
            ->shouldBeCalledOnce()
            ->willReturn(Argument::type(PrePersistEventArgs::class));

        $this->getProphecy(EntityManagerInterface::class)
            ->persist(Argument::exact($interview))
            ->shouldBeCalledOnce();

        $this->getProphecy(EntityManagerInterface::class)
            ->flush()
            ->shouldBeCalledOnce();

        // #5 addFlash()
        $this->getProphecy(RequestStack::class)
            ->getSession()
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Session::class));

        $this->getProphecy(Session::class)
            ->getFlashBag()
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(FlashBag::class));

        $this->getProphecy(FlashBag::class)
            ->add(
                Argument::type('string'),
                Argument::type('string')
            )
            ->shouldBeCalledOnce();

        $this->getProphecy(TranslatorInterface::class)
            ->trans(
                Argument::exact('flash.interview.imported'),
                Argument::type('array'),
                Argument::exact('alerts')
            )
            ->shouldBeCalledOnce()
            ->willReturn('error_message_translated');

        self::assertSame($interview, $this->interviewService->import($interview));
    }
}
