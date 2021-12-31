<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\EncryptionSubscriber;
use App\Service\EncryptionService;
use App\Tests\CustomTestCase;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Prophecy\Argument;
use stdClass;

/**
 * @group unit
 */
class EncryptionSubscriberTest extends CustomTestCase
{
    private ?EncryptionSubscriber $subscriber = null;
    private ?EventManager $dispatcher = null;

    public function setUp(): void
    {
        $this->setProphecies([
            EncryptionService::class,
            EntityManagerInterface::class,
        ]);

        $this->subscriber = new EncryptionSubscriber($this->getReveal(EncryptionService::class));
        $this->dispatcher = new EventManager();
        $this->dispatcher->addEventSubscriber($this->subscriber);
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
            Events::postLoad,
            Events::prePersist,
            Events::preUpdate,
        ], $this->subscriber->getSubscribedEvents());
    }

    public function testPostLoad(): void
    {
        $this->getProphecy(EncryptionService::class)
            ->decrypt(Argument::type(stdClass::class))
            ->shouldBeCalledOnce();

        $event = new PostLoadEventArgs(new stdClass(), $this->getReveal(EntityManagerInterface::class));

        $this->dispatcher->dispatchEvent(Events::postLoad, $event);
    }

    public function testPrePersist(): void
    {
        $this->getProphecy(EncryptionService::class)
            ->encrypt(Argument::type(stdClass::class))
            ->shouldBeCalledOnce();

        $event = new PrePersistEventArgs(new stdClass(), $this->getReveal(EntityManagerInterface::class));

        $this->dispatcher->dispatchEvent(Events::prePersist, $event);
    }

    public function testPreUpdate(): void
    {
        $this->getProphecy(EncryptionService::class)
            ->encrypt(Argument::type(stdClass::class))
            ->shouldBeCalledOnce();

        $changeSet = [];
        $event = new PreUpdateEventArgs(new stdClass(), $this->getReveal(EntityManagerInterface::class), $changeSet);

        $this->dispatcher->dispatchEvent(Events::preUpdate, $event);
    }
}
