<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Constant\SecurityConstant;
use App\Entity\Interview;
use App\Entity\User;
use App\Entity\UserInterview;
use App\Security\Voter\AbstractVoter;
use App\Security\Voter\InterviewVoter;
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
class InterviewVoterTest extends CustomTestCase
{
    public function setUp(): void
    {
        $this->setProphecies([
            Security::class,
            RoleHierarchyInterface::class,
            TokenInterface::class,
            User::class,
            Interview::class,
            Collection::class,
        ]);
    }

    /** @dataProvider providerTestVote */
    public function testVote(string $attribute, bool $isGranted, bool $interview): void
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
            ->isGranted($attribute === AbstractVoter::ACCESS
                ? Argument::exact(SecurityConstant::ROLE_USER)
                : Argument::exact(SecurityConstant::ROLE_ADMIN))
            ->shouldBeCalledOnce()
            ->willReturn($isGranted);

        if ($isGranted && $interview) {
            $userInterview = (new UserInterview())
                ->setUser($this->getReveal(User::class))
                ->setInterview($this->getReveal(Interview::class))
                ->setRoles([SecurityConstant::ROLE_ADMIN])
            ;
            $collection = new ArrayCollection();
            $collection->add($userInterview);

            $this->getProphecy(Interview::class)
                ->getUserInterviews()
                ->shouldBeCalledOnce()
                ->willReturn($collection);

            $this->getProphecy(RoleHierarchyInterface::class)
                ->getReachableRoleNames(Argument::exact([SecurityConstant::ROLE_ADMIN]))
                ->shouldBeCalledOnce()
                ->willReturn([SecurityConstant::ROLE_USER, SecurityConstant::ROLE_ADMIN]);
        }

        $voter = new InterviewVoter(
            $this->getReveal(Security::class),
            $this->getReveal(RoleHierarchyInterface::class)
        );

        $access = $isGranted ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
        self::assertEquals($access, $voter->vote(
            $this->getReveal(TokenInterface::class),
            $interview ? $this->getReveal(Interview::class) : Interview::class,
            [$attribute]
        ));
    }

    public function providerTestVote(): iterable
    {
        foreach (AbstractVoter::ATTRIBUTES as $attribute) {
            yield sprintf('can%s(Interview::class)_DENIED', ucfirst($attribute)) => [$attribute, false, false];
            yield sprintf('can%s(Interview::class)_GRANTED', ucfirst($attribute)) => [$attribute, true, false];
            if (!in_array($attribute, [AbstractVoter::ACCESS, AbstractVoter::CREATE])) {
                yield sprintf('can%s($interview)_DENIED', ucfirst($attribute)) => [$attribute, false, true];
                yield sprintf('can%s($interview)_GRANTED', ucfirst($attribute)) => [$attribute, true, true];
            }
        }
    }

    public function testSupports(): void
    {
        $voter = new InterviewVoter(
            $this->getReveal(Security::class),
            $this->getReveal(RoleHierarchyInterface::class)
        );

        // Unsupported attribute
        self::assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->vote(
            $this->getReveal(TokenInterface::class),
            Interview::class,
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
            Interview::class,
            [AbstractVoter::ACCESS]
        ));
    }
}
