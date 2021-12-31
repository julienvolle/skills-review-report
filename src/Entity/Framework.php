<?php

declare(strict_types=1);

namespace App\Entity;

use App\Constant\SerializerConstant;
use App\Entity\Common\DescriptionTrait;
use App\Entity\Common\GuidTrait;
use App\Entity\Common\NameTrait;
use App\Entity\Common\PrimaryKeyTrait;
use App\Entity\Common\TimestampableTrait;
use App\Repository\FrameworkRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=FrameworkRepository::class)
 *
 * @ORM\Table(
 *     name="framework",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     uniqueConstraints={
 *
 *         @ORM\UniqueConstraint(name="UNIQ_FRAMEWORK_GUID", columns={"guid"})
 *     }
 * )
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Framework
{
    use PrimaryKeyTrait;
    use GuidTrait;
    use NameTrait;
    use DescriptionTrait;
    use TimestampableTrait;

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
     *
     * @ORM\OrderBy({"priority"="asc"})
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_FRAMEWORK,
     *     SerializerConstant::GROUP_HASH
     * })
     */
    protected Collection $levels;

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
     *
     * @ORM\OrderBy({"priority"="asc"})
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_FRAMEWORK,
     *     SerializerConstant::GROUP_HASH
     * })
     */
    protected Collection $categories;

    /**
     * @var Collection|Interview[]
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Interview",
     *     mappedBy="framework"
     * )
     */
    private Collection $interviews;

    /**
     * @var Collection|UserFramework[]
     *
     * @ORM\OneToMany(
     *     targetEntity=UserFramework::class,
     *     mappedBy="framework",
     *     cascade={"persist","remove"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected Collection $userFrameworks;

    public function __construct()
    {
        $this->guid = (string) Uuid::v4();
        $this->levels = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->interviews = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->userFrameworks = new ArrayCollection();
    }

    /**
     * @return Collection|Level[]
     */
    public function getLevels(): Collection
    {
        return $this->levels;
    }

    public function addLevel(Level $level): self
    {
        if (!$this->levels->contains($level)) {
            $level->setFramework($this);
            $this->levels->add($level);
        }

        return $this;
    }

    public function removeLevel(Level $level): self
    {
        if ($this->levels->contains($level)) {
            $level->setFramework(null);
            $this->levels->removeElement($level);
        }

        return $this;
    }

    public function getCategory(string $guid): ?Category
    {
        return $this->getCategories()->filter(static function (Category $category) use ($guid) {
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

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $category->setFramework($this);
            $this->categories->add($category);
        }

        return $this;
    }

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

    public function addInterview(Interview $interview): self
    {
        if (!$this->interviews->contains($interview)) {
            $interview->setFramework($this);
            $this->interviews->add($interview);
        }

        return $this;
    }

    public function removeInterview(Interview $interview): self
    {
        if ($this->interviews->contains($interview)) {
            $interview->setFramework(null);
            $this->interviews->removeElement($interview);
        }

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
            $userFramework->setFramework($this);
        }

        return $this;
    }

    public function removeUserFramework(UserFramework $userFramework): self
    {
        if ($this->userFrameworks->removeElement($userFramework) && $userFramework->getFramework() === $this) {
            // set the owning side to null (unless already changed)
            $userFramework->setFramework(null);
        }

        return $this;
    }
}
