<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\DescriptionTrait;
use App\Entity\Common\GuidTrait;
use App\Entity\Common\NameTrait;
use App\Entity\Common\PrimaryKeyTrait;
use App\Entity\Common\PriorityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(
 *     name="skill",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     indexes={
 *
 *         @ORM\Index(name="IDX_CATEGORY_ID_ON_SKILL", columns={"category_id"})
 *     },
 *     uniqueConstraints={
 *
 *         @ORM\UniqueConstraint(name="UNIQ_SKILL_GUID", columns={"guid"})
 *     }
 * )
 */
class Skill
{
    use PrimaryKeyTrait;
    use GuidTrait;
    use NameTrait;
    use DescriptionTrait;
    use PriorityTrait;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Category",
     *     inversedBy="skills"
     * )
     *
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected ?Category $category = null;

    public function __construct()
    {
        $this->guid = (string) Uuid::v4();
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
