<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FrameworkService;

use App\Constant\SerializerConstant;
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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group unit
 */
class ImportExistingForceTest extends AbstractTestFrameworkService
{
    public function testImportExistingForce(): void
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
            ->shouldBeCalledTimes(2) // search & remove
            ->willReturn($this->getReveal(FrameworkRepository::class));

        $this->getProphecy(FrameworkRepository::class)
            ->findOneByGuid(Argument::exact($framework->getGuid()))
            ->shouldBeCalledOnce()
            ->willReturn($framework);

        // #3 isGranted() for ONE
        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::IMPORT),
                Argument::exact($framework)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        // #4 equals()
        $this->getProphecy(SerializerInterface::class)
            ->serialize(
                Argument::type(Framework::class),
                Argument::exact(SerializerConstant::FORMAT_EXPORT),
                Argument::type('array')
            )
            ->shouldBeCalledTimes(2)
            ->willReturn('hash_1', 'hash_2'); // Is not equals !

        // #5 replace()->remove()
        $this->getProphecy(FrameworkRepository::class)
            ->isUsed(Argument::type(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::DELETE),
                Argument::type(Framework::class)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->getProphecy(EntityManagerInterface::class)
            ->remove(Argument::type(Framework::class))
            ->shouldBeCalledOnce();

        // #6 replace()->save()
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
            ->persist(Argument::type(Framework::class))
            ->shouldBeCalledOnce();

        $this->getProphecy(EntityManagerInterface::class)
            ->flush()
            ->shouldBeCalledTimes(2); // remove & save

        // #7 addFlash()
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
                Argument::exact('flash.framework.updated'),
                Argument::type('array'),
                Argument::exact('alerts')
            )
            ->shouldBeCalledOnce()
            ->willReturn('error_message_translated');

        $this->frameworkService->import($framework, true); // Force
    }
}
