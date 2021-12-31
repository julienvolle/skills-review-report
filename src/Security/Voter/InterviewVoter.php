<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Constant\SecurityConstant;
use App\Entity\Interview;
use App\Entity\UserInterview;
use Symfony\Component\Security\Core\User\UserInterface;

class InterviewVoter extends AbstractVoter
{
    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports(string $attribute, $subject): bool
    {
        if (!\in_array($attribute, self::ATTRIBUTES)) {
            return false;
        }

        if ($subject === Interview::class || $subject instanceof Interview) {
            return true;
        }

        return false;
    }

    protected function canAccess(): bool
    {
        return $this->security->isGranted(SecurityConstant::ROLE_USER); // Can access to report
    }

    /**
     * @param Interview|string   $subject
     * @param UserInterface|null $user
     * @param string             $role
     *
     * @return bool
     */
    protected function common($subject, ?UserInterface $user, string $role = SecurityConstant::ROLE_USER): bool
    {
        if (!$this->security->isGranted($role)) {
            return false;
        }

        if ($subject === Interview::class) {
            return true;
        }

        return !$subject->getUserInterviews()->filter(function (UserInterview $value) use ($role, $user) {
            return ($value->getUser() === $user && $this->isGranted($role, $value->getRoles()));
        })->isEmpty();
    }
}
