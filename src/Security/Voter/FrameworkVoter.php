<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Constant\SecurityConstant;
use App\Entity\Framework;
use App\Entity\UserFramework;
use Symfony\Component\Security\Core\User\UserInterface;

class FrameworkVoter extends AbstractVoter
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

        if ($subject === Framework::class || $subject instanceof Framework) {
            return true;
        }

        return false;
    }

    /**
     * @param Framework|string   $subject
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

        if ($subject === Framework::class) {
            return true;
        }

        return !$subject->getUserFrameworks()->filter(function (UserFramework $value) use ($role, $user) {
            return ($value->getUser() === $user && $this->isGranted($role, $value->getRoles()));
        })->isEmpty();
    }
}
