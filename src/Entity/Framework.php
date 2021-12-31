<?php

declare(strict_types=1);

namespace App\Entity;

use App\Constant\SerializerConstant;
use App\Repository\FrameworkRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=FrameworkRepository::class)
 * @ORM\Table(
 *     name="framework",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="UNIQ_FRAMEWORK_GUID", columns={"guid"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class Framework
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
     * @Groups({SerializerConstant::GROUP_EXPORT_FRAMEWORK, SerializerConstant::GROUP_HASH})
     */
    protected $guid;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_FRAMEWORK, SerializerConstant::GROUP_HASH})
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=500, nullable=true)
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_FRAMEWORK, SerializerConstant::GROUP_HASH})
     */
    protected $description;

    /**
     * @var Collection|Level[]
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Level",
     *     mappedBy="framework",
     *     cascade={"persist","remove"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     * @ORM\OrderBy({"priority"="asc"})
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_FRAMEWORK, SerializerConstant::GROUP_HASH})
     */
    protected $levels;

    /**
     * @var Collection|Category[]
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Category",
     *     mappedBy="framework",
     *     cascade={"persist","remove"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     * @ORM\OrderBy({"priority"="asc"})
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_FRAMEWORK, SerializerConstant::GROUP_HASH})
     */
    protected $categories;

    /**
     * @var Collection|Interview[]
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Interview",
     *     mappedBy="framework"
     * )
     */
    private $interviews;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", name="created_at")
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_FRAMEWORK})
     */
    protected $createdAt;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", name="updated_at")
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_FRAMEWORK})
     */
    protected $updatedAt;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(
     *     targetEntity=UserFramework::class,
     *     mappedBy="framework",
     *     cascade={"persist","remove"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected $userFrameworks;

    public function __construct()
    {
        $this->guid = (string) Uuid::v4();
        $this->levels = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->interviews = new ArrayCollection();
        $this->createdAt = new DateTime('NOW');
        $this->updatedAt = new DateTime('NOW');
        $this->userFrameworks = new ArrayCollection();
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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Level[]
     */
    public function getLevels(): Collection
    {
        return $this->levels;
    }

    /**
     * @param Level $level
     *
     * @return self
     */
    public function addLevel(Level $level): self
    {
        if (!$this->levels->contains($level)) {
            $level->setFramework($this);
            $this->levels->add($level);
        }

        return $this;
    }

    /**
     * @param Level $level
     *
     * @return self
     */
    public function removeLevel(Level $level): self
    {
        if ($this->levels->contains($level)) {
            $level->setFramework(null);
            $this->levels->removeElement($level);
        }

        return $this;
    }

    /**
     * @param string $guid
     *
     * @return Category|null
     */
    public function getCategory(string $guid): ?Category
    {
        return $this->getCategories()->filter(function (Category $category) use ($guid) {
            return $category->getGuid() === $guid;
        })->first();
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    /**
     * @param Category $category
     *
     * @return self
     */
    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $category->setFramework($this);
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * @param Category $category
     *
     * @return self
     */
    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $category->setFramework(null);
            $this->categories->removeElement($category);
        }

        return $this;
    }

    /**
     * @return Collection|Interview[]
     */
    public function getInterviews(): Collection
    {
        return $this->interviews;
    }

    /**
     * @param Interview $interview
     *
     * @return self
     */
    public function addInterview(Interview $interview): self
    {
        if (!$this->interviews->contains($interview)) {
            $interview->setFramework($this);
            $this->interviews->add($interview);
        }

        return $this;
    }

    /**
     * @param Interview $interview
     *
     * @return self
     */
    public function removeInterview(Interview $interview): self
    {
        if ($this->interviews->contains($interview)) {
            $interview->setFramework(null);
            $this->interviews->removeElement($interview);
        }

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
            $userFramework->setFramework($this);
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
            if ($userFramework->getFramework() === $this) {
                $userFramework->setFramework(null);
            }
        }

        return $this;
    }
}
