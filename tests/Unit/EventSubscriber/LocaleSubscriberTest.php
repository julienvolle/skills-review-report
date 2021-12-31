<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\Constant\TranslationConstant;
use App\EventSubscriber\LocaleSubscriber;
use App\Tests\CustomTestCase;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @group unit
 */
class LocaleSubscriberTest extends CustomTestCase
{
    private ?LocaleSubscriber $subscriber = null;
    private ?EventDispatcher $dispatcher = null;

    public function setUp(): void
    {
        $this->setProphecies([
            ParameterBag::class,
            Session::class,
            Request::class,
            RequestEvent::class,
        ]);

        $this->getProphecy(Request::class)->attributes = $this->getReveal(ParameterBag::class);

        $this->subscriber = new LocaleSubscriber(TranslationConstant::DEFAULT_LOCALE);
        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber($this->subscriber);
    }

    public function tearDown(): void
    {
        unset($this->subscriber);
        unset($this->dispatcher);

        parent::tearDown();
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertSame([
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ], $this->subscriber::getSubscribedEvents());
    }

    public function testOnKernelRequestWithLocalSetInRequest(): void
    {
        $this->getProphecy(ParameterBag::class)
            ->get(Argument::exact('_locale'))
            ->shouldBeCalledOnce()
            ->willReturn('fr');

        $this->getProphecy(Session::class)
            ->set(
                Argument::exact('_locale'),
                Argument::exact('fr')
            )
            ->shouldBeCalledOnce();

        $this->getProphecy(Request::class)
            ->hasPreviousSession()
            ->shouldBeCalledOnce()
            ->willReturn(true);
        $this->getProphecy(Request::class)
            ->getSession()
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Session::class));

        $this->getProphecy(RequestEvent::class)
            ->isPropagationStopped()
            ->shouldBeCalledOnce()
            ->willReturn(false);
        $this->getProphecy(RequestEvent::class)
            ->getRequest()
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Request::class));

        $this->dispatcher->dispatch($this->getReveal(RequestEvent::class), KernelEvents::REQUEST);
    }

    public function testOnKernelRequestWithLocalSetInSession(): void
    {
        $this->getProphecy(ParameterBag::class)
            ->get(Argument::exact('_locale'))
            ->shouldBeCalledOnce()
            ->willReturn(null);

        $this->getProphecy(Session::class)
            ->get(
                Argument::exact('_locale'),
                Argument::exact(TranslationConstant::DEFAULT_LOCALE)
            )
            ->shouldBeCalledOnce()
            ->willReturn('fr');

        $this->getProphecy(Request::class)
            ->hasPreviousSession()
            ->shouldBeCalledOnce()
            ->willReturn(true);
        $this->getProphecy(Request::class)
            ->getSession()
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Session::class));
        $this->getProphecy(Request::class)
            ->setLocale(Argument::exact('fr'))
            ->shouldBeCalledOnce();

        $this->getProphecy(RequestEvent::class)
            ->isPropagationStopped()
            ->shouldBeCalledOnce()
            ->willReturn(false);
        $this->getProphecy(RequestEvent::class)
            ->getRequest()
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Request::class));

        $this->dispatcher->dispatch($this->getReveal(RequestEvent::class), KernelEvents::REQUEST);
    }

    public function testOnKernelRequestWithoutPreviousSession(): void
    {
        $this->getProphecy(Request::class)
            ->hasPreviousSession()
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->getProphecy(RequestEvent::class)
            ->isPropagationStopped()
            ->shouldBeCalledOnce()
            ->willReturn(false);
        $this->getProphecy(RequestEvent::class)
            ->getRequest()
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Request::class));

        $this->dispatcher->dispatch($this->getReveal(RequestEvent::class), KernelEvents::REQUEST);
    }
}
