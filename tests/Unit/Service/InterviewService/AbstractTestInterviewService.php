<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\InterviewService;

use App\Repository\InterviewRepository;
use App\Service\FrameworkService;
use App\Service\InterviewService;
use App\Service\SemverService;
use App\Tests\CustomTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractTestInterviewService extends CustomTestCase
{
    protected ?InterviewService $interviewService = null;

    public function setUp(): void
    {
        $this->setProphecies([
            FrameworkService::class,
            EntityManagerInterface::class,
            SerializerInterface::class,
            SemverService::class,
            TranslatorInterface::class,
            Security::class,
            FlashBag::class,
            Session::class,
            RequestStack::class,
            InterviewRepository::class,
            EventDispatcherInterface::class,
        ]);

        $this->interviewService = new InterviewService(
            $this->getReveal(EntityManagerInterface::class),
            $this->getReveal(SerializerInterface::class),
            $this->getReveal(FrameworkService::class),
            $this->getReveal(SemverService::class),
            $this->getReveal(TranslatorInterface::class),
            $this->getReveal(EventDispatcherInterface::class)
        );
        $this->interviewService->setSecurity($this->getReveal(Security::class));
        $this->interviewService->setRequestStack($this->getReveal(RequestStack::class));
    }

    public function tearDown(): void
    {
        unset($this->interviewService);

        parent::tearDown();
    }
}
