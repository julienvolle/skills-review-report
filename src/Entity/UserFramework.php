<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\PrimaryKeyTrait;
use App\Repository\UserFrameworkRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserFrameworkRepository::class)
 *
 * @ORM\Table(
 *     name="user_framework",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     indexes={
 *
 *         @ORM\Index(name="IDX_USER_ID_ON_UF", columns={"user_id"}),
 *         @ORM\Index(name="IDX_FRAMEWORK_ID_ON_UF", columns={"framework_id"})
 *     },
 *     uniqueConstraints={
 *
 *         @ORM\UniqueConstraint(name="UNIQ_UF_IDS", columns={"user_id", "framework_id"})
 *     }
 * )
 */
class UserFramework
{
    use PrimaryKeyTrait;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userFrameworks")
     */
    protected ?UserInterface $user = null;

    /**
     * @ORM\ManyToOne(targetEntity=Framework::class, inversedBy="userFrameworks")
     *
     * @ORM\JoinColumn(name="framework_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected ?Framework $framework = null;

    /**
     * @ORM\Column(type="json")
     */
    protected array $roles = [];

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFramework(): ?Framework
    {
        return $this->framework;
    }

    public function setFramework(?Framework $framework): self
    {
        $this->framework = $framework;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRoles(array $roles): self
    {
        $this->roles = \array_unique(\array_merge($this->roles, $roles), SORT_REGULAR);

        return $this;
    }
}
