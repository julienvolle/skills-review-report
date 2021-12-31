<?php

declare(strict_types=1);

namespace App\Tests\Unit\Request;

use App\Request\FlashBagAwareTrait;
use App\Tests\CustomTestCase;
use LogicException;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @group unit
 */
class FlashBagAwareTest extends CustomTestCase
{
    private ?object $object = null;

    public function setUp(): void
    {
        $this->setProphecies([
            FlashBag::class,
            Session::class,
            RequestStack::class,
        ]);

        $this->object = new class () {
            use FlashBagAwareTrait;
        };
    }

    public function tearDown(): void
    {
        unset($this->object);

        parent::tearDown();
    }

    public function testTrait(): void
    {
        self::assertTrue(method_exists($this->object, 'setRequestStack'));
        self::assertTrue(method_exists($this->object, 'addFlash'));
    }

    public function testAddFlash(): void
    {
        $this->getProphecy(FlashBag::class)
            ->add(
                Argument::type('string'),
                Argument::type('string')
            )
            ->shouldBeCalledOnce();

        $this->getProphecy(Session::class)
            ->getFlashBag()
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(FlashBag::class));

        $this->getProphecy(RequestStack::class)
            ->getSession()
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Session::class));

        $this->object->setRequestStack($this->getReveal(RequestStack::class));
        $this->object->addFlash('type', 'message');
    }

    public function testAddFlashWithoutRequestStack(): void
    {
        $this->expectException(LogicException::class);

        $this->object->addFlash('type', 'message');
    }

    public function testAddFlashWithoutSession(): void
    {
        $this->expectException(LogicException::class);

        $this->getProphecy(RequestStack::class)
            ->getSession()
            ->shouldBeCalledOnce()
            ->willThrow(SessionNotFoundException::class);

        $this->object->setRequestStack($this->getReveal(RequestStack::class));
        $this->object->addFlash('type', 'message');
    }
}
