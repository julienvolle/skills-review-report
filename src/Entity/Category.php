<?php

declare(strict_types=1);

namespace App\Entity;

use App\Constant\SerializerConstant;
use App\Entity\Common\DescriptionTrait;
use App\Entity\Common\GuidTrait;
use App\Entity\Common\NameTrait;
use App\Entity\Common\PrimaryKeyTrait;
use App\Entity\Common\PriorityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(
 *     name="category",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     indexes={
 *
 *         @ORM\Index(name="IDX_FRAMEWORK_ID_ON_CATEGORY", columns={"framework_id"})
 *     },
 *     uniqueConstraints={
 *
 *         @ORM\UniqueConstraint(name="UNIQ_CATEGORY_GUID", columns={"guid"})
 *     }
 * )
 */
class Category
{
    use PrimaryKeyTrait;
    use GuidTrait;
    use NameTrait;
    use DescriptionTrait;
    use PriorityTrait;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Framework",
     *     inversedBy="categories"
     * )
     *
     * @ORM\JoinColumn(name="framework_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected ?Framework $framework = null;

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
     *
     * @ORM\OrderBy({"priority"="asc"})
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_FRAMEWORK,
     *     SerializerConstant::GROUP_HASH
     * })
     */
    protected Collection $skills;

    public function __construct()
    {
        $this->guid = (string) Uuid::v4();
        $this->skills = new ArrayCollection();
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

    public function getSkill(string $guid): ?Skill
    {
        return $this->getSkills()->filter(static function (Skill $skill) use ($guid) {
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

    public function addSkill(Skill $skill): self
    {
        if (!$this->skills->contains($skill)) {
            $skill->setCategory($this);
            $this->skills->add($skill);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): self
    {
        if ($this->skills->contains($skill)) {
            $skill->setCategory(null);
            $this->skills->removeElement($skill);
        }

        return $this;
    }
}
