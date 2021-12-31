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

    /** @var Security */
    protected $security;

    /** @var RoleHierarchyInterface */
    protected $roleHierarchy;

    /**
     * @param Security               $security
     * @param RoleHierarchyInterface $roleHierarchy
     */
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
     *
     * @return bool
     */
    protected function isGranted(string $role, array $roles): bool
    {
        return in_array($role, $this->roleHierarchy->getReachableRoleNames($roles), true);
    }

    /**
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) on parameter $attribute
     */
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
                return $this->canAccess();
            case self::CREATE:
                return $this->canCreate();
            case self::UPDATE:
                return $this->canUpdate($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
            case self::IMPORT:
                return $this->canImport($subject, $user);
            case self::EXPORT:
                return $this->canExport($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    abstract protected function canAccess(): bool;
    abstract protected function canCreate(): bool;
    abstract protected function canUpdate($subject, $user): bool;
    abstract protected function canDelete($subject, $user): bool;
    abstract protected function canImport($subject, $user): bool;
    abstract protected function canExport($subject, $user): bool;
}
