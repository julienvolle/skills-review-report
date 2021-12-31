<?php

declare(strict_types=1);

namespace App\Entity;

use App\Constant\SecurityConstant;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(
 *     name="user",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="UNIQ_USER_GUID", columns={"guid"}),
 *         @ORM\UniqueConstraint(name="UNIQ_USER_EMAIL", columns={"email"})
 *     }
 * )
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
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
     * @var string
     *
     * @ORM\Column(type="guid", unique=true)
     */
    protected $guid;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $email;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @var string The hashed password
     *
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(
     *     targetEntity=UserFramework::class,
     *     mappedBy="user",
     *     cascade={"persist","remove"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected $userFrameworks;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(
     *     targetEntity=UserInterview::class,
     *     mappedBy="user",
     *     cascade={"persist","remove"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected $userInterviews;

    public function __construct()
    {
        $this->guid = (string) Uuid::v4();
        $this->userFrameworks = new ArrayCollection();
        $this->userInterviews = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getGuid(): string
    {
        return $this->guid;
    }

    /**
     * @param string $guid
     *
     * @return self
     */
    public function setGuid(string $guid): self
    {
        $this->guid = $guid;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     *
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->getEmail() ?? '';
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return $this->getEmail() ?? '';
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = SecurityConstant::ROLE_USER;

        return array_unique($roles);
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

    /**
     * @param string $password
     *
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     *
     * @return ?string
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|UserFramework[]
     */
    public function getUserFrameworks(): Collection
    {
        return $this->userFrameworks;
    }

    /**
     * @param UserFramework $userFramework
     *
     * @return self
     */
    public function addUserFramework(UserFramework $userFramework): self
    {
        if (!$this->userFrameworks->contains($userFramework)) {
            $this->userFrameworks[] = $userFramework;
            $userFramework->setUser($this);
        }

        return $this;
    }

    /**
     * @param UserFramework $userFramework
     *
     * @return self
     */
    public function removeUserFramework(UserFramework $userFramework): self
    {
        if ($this->userFrameworks->removeElement($userFramework)) {
            // set the owning side to null (unless already changed)
            if ($userFramework->getUser() === $this) {
                $userFramework->setUser(null);
            }
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

    /**
     * @param UserInterview $userInterview
     *
     * @return self
     */
    public function addUserInterview(UserInterview $userInterview): self
    {
        if (!$this->userInterviews->contains($userInterview)) {
            $this->userInterviews[] = $userInterview;
            $userInterview->setUser($this);
        }

        return $this;
    }

    /**
     * @param UserInterview $userInterview
     *
     * @return self
     */
    public function removeUserInterview(UserInterview $userInterview): self
    {
        if ($this->userInterviews->removeElement($userInterview)) {
            // set the owning side to null (unless already changed)
            if ($userInterview->getUser() === $this) {
                $userInterview->setUser(null);
            }
        }

        return $this;
    }
}
