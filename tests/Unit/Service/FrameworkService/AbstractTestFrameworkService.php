<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FrameworkService;

use App\Repository\FrameworkRepository;
use App\Service\FrameworkService;
use App\Service\SemverService;
use App\Tests\CustomTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractTestFrameworkService extends CustomTestCase
{
    protected ?FrameworkService $frameworkService = null;

    public function setUp(): void
    {
        $this->setProphecies([
            EntityManagerInterface::class,
            SerializerInterface::class,
            SemverService::class,
            TranslatorInterface::class,
            Security::class,
            FlashBag::class,
            Session::class,
            RequestStack::class,
            FrameworkRepository::class,
        ]);

        $this->frameworkService = new FrameworkService(
            $this->getReveal(EntityManagerInterface::class),
            $this->getReveal(SerializerInterface::class),
            $this->getReveal(SemverService::class),
            $this->getReveal(TranslatorInterface::class)
        );
        $this->frameworkService->setSecurity($this->getReveal(Security::class));
        $this->frameworkService->setRequestStack($this->getReveal(RequestStack::class));
    }

    public function tearDown(): void
    {
        unset($this->frameworkService);

        parent::tearDown();
    }
}
