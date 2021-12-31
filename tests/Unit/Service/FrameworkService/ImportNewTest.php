<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FrameworkService;

use App\Entity\Framework;
use App\Entity\User;
use App\Repository\FrameworkRepository;
use App\Security\Voter\AbstractVoter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group unit
 */
class ImportNewTest extends AbstractTestFrameworkService
{
    public function testImportNew(): void
    {
        $framework = $this->getMockBuilder(Framework::class)->getMock();
        $framework->method('getId')->willReturn(null);
        $framework->method('getGuid')->willReturn((string) Uuid::v4());
        $framework->method('getUserFrameworks')->willReturn(new ArrayCollection());

        // #1 isGranted() for ALL
        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::IMPORT),
                Argument::exact(Framework::class)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        // #2 search()
        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(FrameworkRepository::class));

        $this->getProphecy(FrameworkRepository::class)
            ->findOneByGuid(Argument::exact($framework->getGuid()))
            ->shouldBeCalledOnce()
            ->willReturn(null);

        // #3 save()
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

        // #4 addFlash()
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
                Argument::exact('flash.framework.imported'),
                Argument::type('array'),
                Argument::exact('alerts')
            )
            ->shouldBeCalledOnce()
            ->willReturn('error_message_translated');

        self::assertSame($framework, $this->frameworkService->import($framework));
    }
}
