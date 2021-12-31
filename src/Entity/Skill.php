<?php

declare(strict_types=1);

namespace App\Entity;

use App\Constant\SerializerConstant;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="skill",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     indexes={
 *         @ORM\Index(name="IDX_CATEGORY_ID_ON_SKILL", columns={"category_id"})
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="UNIQ_SKILL_GUID", columns={"guid"})
 *     }
 * )
 */
class Skill
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
     * @var Category|null
     *
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Category",
     *     inversedBy="skills"
     * )
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $category;

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

    public function __construct()
    {
        $this->guid = (string) Uuid::v4();
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
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category|null $category
     *
     * @return self
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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
}
