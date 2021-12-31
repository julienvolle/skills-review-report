<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Constant\SecurityConstant;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractVoter extends Voter
{
    public const ACCESS = 'access'; // Access to framework or interview sections
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
    public const IMPORT = 'import';
    public const EXPORT = 'export';

    public const ATTRIBUTES = [
        self::ACCESS,
        self::CREATE,
        self::UPDATE,
        self::DELETE,
        self::IMPORT,
        self::EXPORT,
    ];

    protected Security $security;
    protected RoleHierarchyInterface $roleHierarchy;

    public function __construct(Security $security, RoleHierarchyInterface $roleHierarchy)
    {
        $this->security = $security;
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * Check role with hierarchy definition
     *
     * @param string $role  Role required
     * @param array  $roles Roles available
     */
    protected function isGranted(string $role, array $roles): bool
    {
        return \in_array($role, $this->roleHierarchy->getReachableRoleNames($roles), true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($this->security->isGranted(SecurityConstant::ROLE_SUPER_ADMIN)) {
            return true;
        }

        switch ($attribute) {
            case self::ACCESS:
                $result = $this->canAccess();
                break;
            case self::CREATE:
                $result = $this->canCreate();
                break;
            case self::UPDATE:
                $result = $this->canUpdate($subject, $user);
                break;
            case self::DELETE:
                $result = $this->canDelete($subject, $user);
                break;
            case self::IMPORT:
                $result = $this->canImport($subject, $user);
                break;
            case self::EXPORT:
                $result = $this->canExport($subject, $user);
                break;
            default:
                throw new LogicException('This code should not be reached!');
        }

        return $result;
    }

    protected function canAccess(): bool
    {
        return $this->security->isGranted(SecurityConstant::ROLE_ADMIN);
    }

    protected function canCreate(): bool
    {
        return $this->security->isGranted(SecurityConstant::ROLE_ADMIN);
    }

    protected function canUpdate($subject, ?UserInterface $user): bool
    {
        return $this->common($subject, $user, SecurityConstant::ROLE_ADMIN);
    }

    protected function canDelete($subject, ?UserInterface $user): bool
    {
        return $this->common($subject, $user, SecurityConstant::ROLE_ADMIN);
    }

    protected function canImport($subject, ?UserInterface $user): bool
    {
        return $this->common($subject, $user, SecurityConstant::ROLE_ADMIN);
    }

    protected function canExport($subject, ?UserInterface $user): bool
    {
        return $this->common($subject, $user, SecurityConstant::ROLE_ADMIN);
    }

    abstract protected function common(
        $subject,
        ?UserInterface $user,
        string $role = SecurityConstant::ROLE_USER
    ): bool;
}
