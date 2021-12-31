<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\PrimaryKeyTrait;
use App\Repository\UserInterviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserInterviewRepository::class)
 *
 * @ORM\Table(
 *     name="user_interview",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     indexes={
 *
 *         @ORM\Index(name="IDX_USER_ID_ON_UI", columns={"user_id"}),
 *         @ORM\Index(name="IDX_INTERVIEW_ID_ON_UI", columns={"interview_id"})
 *     },
 *     uniqueConstraints={
 *
 *         @ORM\UniqueConstraint(name="UNIQ_UI_IDS", columns={"user_id", "interview_id"})
 *     }
 * )
 */
class UserInterview
{
    use PrimaryKeyTrait;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userInterviews")
     */
    protected ?UserInterface $user = null;

    /**
     * @ORM\ManyToOne(targetEntity=Interview::class, inversedBy="userInterviews")
     *
     * @ORM\JoinColumn(name="interview_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected ?Interview $interview = null;

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

    public function getInterview(): ?Interview
    {
        return $this->interview;
    }

    public function setInterview(?Interview $interview): self
    {
        $this->interview = $interview;

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
