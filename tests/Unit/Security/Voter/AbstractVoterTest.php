<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Constant\SecurityConstant;
use App\Entity\User;
use App\Security\Voter\AbstractVoter;
use App\Tests\CustomTestCase;
use LogicException;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @group unit
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AbstractVoterTest extends CustomTestCase
{
    private ?AbstractVoter $voter = null;

    public function setUp(): void
    {
        $this->setProphecies([
            Security::class,
            RoleHierarchyInterface::class,
            TokenInterface::class,
            User::class,
        ]);

        $security = $this->getReveal(Security::class);
        $roleHierarchy = $this->getReveal(RoleHierarchyInterface::class);
        $this->voter = new class ($security, $roleHierarchy) extends AbstractVoter {
            protected function canAccess(): bool
            {
                return true;
            }
            protected function canCreate(): bool
            {
                return true;
            }
            protected function canUpdate($subject, ?UserInterface $user): bool
            {
                return true;
            }
            protected function canDelete($subject, ?UserInterface $user): bool
            {
                return true;
            }
            protected function canImport($subject, ?UserInterface $user): bool
            {
                return true;
            }
            protected function canExport($subject, ?UserInterface $user): bool
            {
                return true;
            }
            protected function supports(string $attribute, $subject): bool
            {
                return true;
            }
            protected function common($subject, ?UserInterface $user, string $role = SecurityConstant::ROLE_USER): bool
            {
                return true;
            }
        };
    }

    public function tearDown(): void
    {
        unset($this->voter);

        parent::tearDown();
    }

    public function testRootAccess(): void
    {
        $this->getProphecy(TokenInterface::class)
            ->getUser()
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(User::class));

        $this->getProphecy(Security::class)
            ->isGranted(Argument::exact(SecurityConstant::ROLE_SUPER_ADMIN))
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $token = $this->getReveal(TokenInterface::class);
        self::assertEquals(VoterInterface::ACCESS_GRANTED, $this->voter->vote($token, '', ['']));
    }

    public function testInvalidAttribute(): void
    {
        $this->getProphecy(TokenInterface::class)
            ->getUser()
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(User::class));

        $this->getProphecy(Security::class)
            ->isGranted(Argument::exact(SecurityConstant::ROLE_SUPER_ADMIN))
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('This code should not be reached!');

        $this->voter->vote($this->getReveal(TokenInterface::class), '', ['']);
    }
}
