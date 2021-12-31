<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserFrameworkRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserFrameworkRepository::class)
 * @ORM\Table(
 *     name="user_framework",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     indexes={
 *         @ORM\Index(name="IDX_USER_ID_ON_UF", columns={"user_id"}),
 *         @ORM\Index(name="IDX_FRAMEWORK_ID_ON_UF", columns={"framework_id"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="UNIQ_UF_IDS", columns={"user_id", "framework_id"})
 *     }
 * )
 */
class UserFramework
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var UserInterface|null
     *
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userFrameworks")
     */
    protected $user;

    /**
     * @var Framework|null
     *
     * @ORM\ManyToOne(targetEntity=Framework::class, inversedBy="userFrameworks")
     * @ORM\JoinColumn(name="framework_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $framework;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * @param UserInterface|null $user
     *
     * @return self
     */
    public function setUser(?UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Framework|null
     */
    public function getFramework(): ?Framework
    {
        return $this->framework;
    }

    /**
     * @param Framework|null $framework
     *
     * @return self
     */
    public function setFramework(?Framework $framework): self
    {
        $this->framework = $framework;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     *
     * @return self
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @param array $roles
     *
     * @return self
     */
    public function addRoles(array $roles): self
    {
        $this->roles = array_unique(array_merge($this->roles, $roles), SORT_REGULAR);

        return $this;
    }
}
