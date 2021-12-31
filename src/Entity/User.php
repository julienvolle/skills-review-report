<?php

declare(strict_types=1);

namespace App\Entity;

use App\Constant\SecurityConstant;
use App\Entity\Common\PrimaryKeyTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 *
 * @ORM\Table(
 *     name="user",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     uniqueConstraints={
 *
 *         @ORM\UniqueConstraint(name="UNIQ_USER_GUID", columns={"guid"}),
 *         @ORM\UniqueConstraint(name="UNIQ_USER_EMAIL", columns={"email"})
 *     }
 * )
 */
class User extends AbstractUser
{
    use PrimaryKeyTrait;

    /**
     * @ORM\Column(type="guid", unique=true)
     */
    protected string $guid;

    /**
     * @ORM\Column(type="string", length=500, unique=true)
     */
    protected ?string $email = null;

    /**
     * @ORM\Column(type="json")
     */
    protected array $roles = [];

    /**
     * The hashed password
     *
     * @ORM\Column(type="string", length=500)
     */
    protected ?string $password = null;

    /**
     * @var Collection|UserFramework[]
     *
     * @ORM\OneToMany(
     *     targetEntity=UserFramework::class,
     *     mappedBy="user",
     *     cascade={"persist","remove"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected Collection $userFrameworks;

    /**
     * @var Collection|UserInterview[]
     *
     * @ORM\OneToMany(
     *     targetEntity=UserInterview::class,
     *     mappedBy="user",
     *     cascade={"persist","remove"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected Collection $userInterviews;

    public function __construct()
    {
        $this->guid = (string) Uuid::v4();
        $this->userFrameworks = new ArrayCollection();
        $this->userInterviews = new ArrayCollection();
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function setGuid(string $guid): self
    {
        $this->guid = $guid;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = SecurityConstant::ROLE_USER;

        return \array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Collection|UserFramework[]
     */
    public function getUserFrameworks(): Collection
    {
        return $this->userFrameworks;
    }

    public function addUserFramework(UserFramework $userFramework): self
    {
        if (!$this->userFrameworks->contains($userFramework)) {
            $this->userFrameworks[] = $userFramework;
            $userFramework->setUser($this);
        }

        return $this;
    }

    public function removeUserFramework(UserFramework $userFramework): self
    {
        if ($this->userFrameworks->removeElement($userFramework) && $userFramework->getUser() === $this) {
            // set the owning side to null (unless already changed)
            $userFramework->setUser(null);
        }

        return $this;
    }

    /**
     * @return Collection|UserInterview[]
     */
    public function getUserInterviews(): Collection
    {
        return $this->userInterviews;
    }

    public function addUserInterview(UserInterview $userInterview): self
    {
        if (!$this->userInterviews->contains($userInterview)) {
            $this->userInterviews[] = $userInterview;
            $userInterview->setUser($this);
        }

        return $this;
    }

    public function removeUserInterview(UserInterview $userInterview): self
    {
        if ($this->userInterviews->removeElement($userInterview) && $userInterview->getUser() === $this) {
            // set the owning side to null (unless already changed)
            $userInterview->setUser(null);
        }

        return $this;
    }
}
