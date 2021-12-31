<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Constant\SecurityConstant;
use App\Entity\Framework;
use App\Entity\User;
use App\Entity\UserFramework;
use App\Security\Voter\AbstractVoter;
use App\Security\Voter\FrameworkVoter;
use App\Tests\CustomTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @group unit
 */
class FrameworkVoterTest extends CustomTestCase
{
    public function setUp(): void
    {
        $this->setProphecies([
            Security::class,
            RoleHierarchyInterface::class,
            TokenInterface::class,
            User::class,
            Framework::class,
            Collection::class,
        ]);
    }

    /** @dataProvider providerTestVote */
    public function testVote(string $attribute, bool $isGranted, bool $framework): void
    {
        $this->getProphecy(TokenInterface::class)
            ->getUser()
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(User::class));

        // Skip root access
        $this->getProphecy(Security::class)
            ->isGranted(Argument::exact(SecurityConstant::ROLE_SUPER_ADMIN))
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->getProphecy(Security::class)
            ->isGranted(Argument::exact(SecurityConstant::ROLE_ADMIN))
            ->shouldBeCalledOnce()
            ->willReturn($isGranted);

        if ($isGranted && $framework) {
            $userFramework = (new UserFramework())
                ->setUser($this->getReveal(User::class))
                ->setFramework($this->getReveal(Framework::class))
                ->setRoles([SecurityConstant::ROLE_ADMIN])
            ;
            $collection = new ArrayCollection();
            $collection->add($userFramework);

            $this->getProphecy(Framework::class)
                ->getUserFrameworks()
                ->shouldBeCalledOnce()
                ->willReturn($collection);

            $this->getProphecy(RoleHierarchyInterface::class)
                ->getReachableRoleNames(Argument::exact([SecurityConstant::ROLE_ADMIN]))
                ->shouldBeCalledOnce()
                ->willReturn([SecurityConstant::ROLE_USER, SecurityConstant::ROLE_ADMIN]);
        }

        $voter = new FrameworkVoter(
            $this->getReveal(Security::class),
            $this->getReveal(RoleHierarchyInterface::class)
        );

        $access = $isGranted ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
        self::assertEquals($access, $voter->vote(
            $this->getReveal(TokenInterface::class),
            $framework ? $this->getReveal(Framework::class) : Framework::class,
            [$attribute]
        ));
    }

    public function providerTestVote(): iterable
    {
        foreach (AbstractVoter::ATTRIBUTES as $attribute) {
            yield sprintf('can%s(Framework::class)_DENIED', ucfirst($attribute)) => [$attribute, false, false];
            yield sprintf('can%s(Framework::class)_GRANTED', ucfirst($attribute)) => [$attribute, true, false];
            if (!in_array($attribute, [AbstractVoter::ACCESS, AbstractVoter::CREATE])) {
                yield sprintf('can%s($framework)_DENIED', ucfirst($attribute)) => [$attribute, false, true];
                yield sprintf('can%s($framework)_GRANTED', ucfirst($attribute)) => [$attribute, true, true];
            }
        }
    }

    public function testSupports(): void
    {
        $voter = new FrameworkVoter(
            $this->getReveal(Security::class),
            $this->getReveal(RoleHierarchyInterface::class)
        );

        // Unsupported attribute
        self::assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->vote(
            $this->getReveal(TokenInterface::class),
            Framework::class,
            ['']
        ));

        // Unsupported subject
        self::assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->vote(
            $this->getReveal(TokenInterface::class),
            '',
            [AbstractVoter::ACCESS]
        ));

        // Supported attribute & subject
        $this->getProphecy(TokenInterface::class)
            ->getUser()
            ->shouldBeCalledOnce()
            ->willReturn('');
        self::assertEquals(VoterInterface::ACCESS_DENIED, $voter->vote(
            $this->getReveal(TokenInterface::class),
            Framework::class,
            [AbstractVoter::ACCESS]
        ));
    }
}
