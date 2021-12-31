<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FrameworkService;

use App\Entity\Framework;
use App\Exception\Framework\FrameworkImportException;
use App\Repository\FrameworkRepository;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\Uuid;

/**
 * @group unit
 */
class ImportDenyAccessTest extends AbstractTestFrameworkService
{
    public function testImportDenyAccessAll(): void
    {
        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::IMPORT),
                Argument::exact(Framework::class)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false); // isGranted() for ALL = KO

        $this->expectException(FrameworkImportException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->frameworkService->import(new Framework());
    }

    public function testImportDenyAccessOne(): void
    {
        $framework = (new Framework())->setGuid((string) Uuid::v4());

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::IMPORT),
                Argument::exact(Framework::class)
            )
            ->shouldBeCalledOnce()
            ->willReturn(true); // isGranted() for ALL = OK

        $this->getProphecy(EntityManagerInterface::class)
            ->getRepository(Argument::exact(Framework::class))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(FrameworkRepository::class));

        $this->getProphecy(FrameworkRepository::class)
            ->findOneByGuid(Argument::exact($framework->getGuid()))
            ->shouldBeCalledOnce()
            ->willReturn($framework);

        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact(AbstractVoter::IMPORT),
                Argument::exact($framework)
            )
            ->shouldBeCalledOnce()
            ->willReturn(false); // isGranted() for ONE = KO

        $this->expectException(FrameworkImportException::class);
        $this->expectExceptionMessage('Access Denied.');

        $this->frameworkService->import($framework);
    }
}
