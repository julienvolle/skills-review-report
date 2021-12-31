<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserInterviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserInterviewRepository::class)
 * @ORM\Table(
 *     name="user_interview",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     indexes={
 *         @ORM\Index(name="IDX_USER_ID_ON_UI", columns={"user_id"}),
 *         @ORM\Index(name="IDX_INTERVIEW_ID_ON_UI", columns={"interview_id"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="UNIQ_UI_IDS", columns={"user_id", "interview_id"})
 *     }
 * )
 */
class UserInterview
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userInterviews")
     */
    protected $user;

    /**
     * @var Interview|null
     *
     * @ORM\ManyToOne(targetEntity=Interview::class, inversedBy="userInterviews")
     * @ORM\JoinColumn(name="interview_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $interview;

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
     * @return Interview|null
     */
    public function getInterview(): ?Interview
    {
        return $this->interview;
    }

    /**
     * @param Interview|null $interview
     *
     * @return self
     */
    public function setInterview(?Interview $interview): self
    {
        $this->interview = $interview;

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
