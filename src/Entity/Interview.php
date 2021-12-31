<?php

declare(strict_types=1);

namespace App\Entity;

use App\Constant\SerializerConstant;
use App\Repository\InterviewRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=InterviewRepository::class)
 * @ORM\Table(
 *     name="interview",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     indexes={
 *         @ORM\Index(name="IDX_FRAMEWORK_ID_ON_INTERVIEW", columns={"framework_id"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="UNIQ_INTERVIEW_GUID", columns={"guid"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Interview
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="guid", unique=true)
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_INTERVIEW, SerializerConstant::GROUP_HASH})
     */
    protected $guid;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_INTERVIEW, SerializerConstant::GROUP_HASH})
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_INTERVIEW, SerializerConstant::GROUP_HASH})
     */
    protected $lastname;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_INTERVIEW, SerializerConstant::GROUP_HASH})
     */
    protected $firstname;

    /**
     * @var Framework|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Framework", inversedBy="interviews")
     * @ORM\JoinColumn(name="framework_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_INTERVIEW})
     */
    protected $framework;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_INTERVIEW, SerializerConstant::GROUP_HASH})
     */
    protected $result = [];

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", name="created_at")
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_INTERVIEW})
     */
    protected $createdAt;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", name="updated_at")
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_INTERVIEW})
     */
    protected $updatedAt;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(
     *     targetEntity=UserInterview::class,
     *     mappedBy="interview",
     *     cascade={"persist","remove"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected $userInterviews;

    public function __construct()
    {
        $this->guid = (string) Uuid::v4();
        $this->createdAt = new DateTime('NOW');
        $this->updatedAt = new DateTime('NOW');
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
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     *
     * @return self
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     *
     * @return self
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

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
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @param array $result
     *
     * @return self
     */
    public function setResult(array $result): self
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface $createdAt
     *
     * @return self
     */
    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeInterface $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTime('NOW');
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
            $userInterview->setInterview($this);
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
            if ($userInterview->getInterview() === $this) {
                $userInterview->setInterview(null);
            }
        }

        return $this;
    }
}
