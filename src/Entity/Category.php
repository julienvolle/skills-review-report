<?php

declare(strict_types=1);

namespace App\Entity;

use App\Constant\SerializerConstant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="category",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     indexes={
 *         @ORM\Index(name="IDX_FRAMEWORK_ID_ON_CATEGORY", columns={"framework_id"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="UNIQ_CATEGORY_GUID", columns={"guid"})
 *     }
 * )
 */
class Category
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
     * @var Framework|null
     *
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Framework",
     *     inversedBy="categories"
     * )
     * @ORM\JoinColumn(name="framework_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $framework;

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
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_FRAMEWORK, SerializerConstant::GROUP_HASH})
     */
    protected $priority = 1;

    /**
     * @var Collection|Skill[]
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Skill",
     *     mappedBy="category",
     *     cascade={"persist","remove"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     * @ORM\OrderBy({"priority"="asc"})
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_FRAMEWORK, SerializerConstant::GROUP_HASH})
     */
    protected $skills;

    public function __construct()
    {
        $this->guid = (string) Uuid::v4();
        $this->skills = new ArrayCollection();
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
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return self
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @param string $guid
     *
     * @return Skill|null
     */
    public function getSkill(string $guid): ?Skill
    {
        return $this->getSkills()->filter(function (Skill $skill) use ($guid) {
            return $skill->getGuid() === $guid;
        })->first();
    }

    /**
     * @return Collection|Skill[]
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    /**
     * @param Skill $skill
     *
     * @return self
     */
    public function addSkill(Skill $skill): self
    {
        if (!$this->skills->contains($skill)) {
            $skill->setCategory($this);
            $this->skills->add($skill);
        }

        return $this;
    }

    /**
     * @param Skill $skill
     *
     * @return self
     */
    public function removeSkill(Skill $skill): self
    {
        if ($this->skills->contains($skill)) {
            $skill->setCategory(null);
            $this->skills->removeElement($skill);
        }

        return $this;
    }
}
