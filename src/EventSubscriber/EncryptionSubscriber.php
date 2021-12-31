<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\EncryptionService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class EncryptionSubscriber implements EventSubscriber
{
    private EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad,
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $this->encryptionService->decrypt($args->getObject());
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->encryptionService->encrypt($args->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->encryptionService->encrypt($args->getObject());
    }
}
