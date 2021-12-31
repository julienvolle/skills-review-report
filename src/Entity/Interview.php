<?php

declare(strict_types=1);

namespace App\Entity;

use App\Annotation\Encryption;
use App\Constant\SecurityConstant;
use App\Constant\SerializerConstant;
use App\Entity\Common\GuidTrait;
use App\Entity\Common\PrimaryKeyTrait;
use App\Entity\Common\SecuredEntityTrait;
use App\Entity\Common\TimestampableTrait;
use App\Repository\InterviewRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=InterviewRepository::class)
 *
 * @ORM\Table(
 *     name="interview",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     indexes={
 *
 *         @ORM\Index(name="IDX_FRAMEWORK_ID_ON_INTERVIEW", columns={"framework_id"})
 *     },
 *     uniqueConstraints={
 *
 *         @ORM\UniqueConstraint(name="UNIQ_INTERVIEW_GUID", columns={"guid"})
 *     }
 * )
 *
 * @ORM\HasLifecycleCallbacks()
 */
class Interview
{
    use PrimaryKeyTrait;
    use GuidTrait;
    use SecuredEntityTrait;
    use TimestampableTrait;

    /**
     * @ORM\Column(type="string", length=500)
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_INTERVIEW,
     *     SerializerConstant::GROUP_HASH
     * })
     */
    protected ?string $title = null;

    /**
     * @ORM\Column(type="string", length=500)
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_INTERVIEW,
     *     SerializerConstant::GROUP_HASH
     * })
     *
     * @Encryption(name=SecurityConstant::ENCRYPTION_AES256, maxLength=500)
     */
    protected ?string $lastname = null;

    /**
     * @ORM\Column(type="string", length=500)
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_INTERVIEW,
     *     SerializerConstant::GROUP_HASH
     * })
     *
     * @Encryption(name=SecurityConstant::ENCRYPTION_AES256, maxLength=500)
     */
    protected ?string $firstname = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Framework", inversedBy="interviews")
     *
     * @ORM\JoinColumn(name="framework_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_INTERVIEW
     * })
     */
    protected ?Framework $framework = null;

    /**
     * @ORM\Column(type="json")
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_INTERVIEW,
     *     SerializerConstant::GROUP_HASH
     * })
     */
    protected array $result = [];

    /**
     * @var Collection|UserInterview[]
     *
     * @ORM\OneToMany(
     *     targetEntity=UserInterview::class,
     *     mappedBy="interview",
     *     cascade={"persist","remove"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     */
    protected Collection $userInterviews;

    public function __construct()
    {
        $this->guid = (string) Uuid::v4();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->userInterviews = new ArrayCollection();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
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

    public function getResult(): array
    {
        return $this->result;
    }

    public function setResult(array $result): self
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return Collection|UserInterview[]
     */
    public function getUserInterviews(): Collection
    {
        return $this->userInterviews;
    }

    public function addUserInterview(UserInterview $userInterview): self
    {
        if (!$this->userInterviews->contains($userInterview)) {
            $this->userInterviews[] = $userInterview;
            $userInterview->setInterview($this);
        }

        return $this;
    }

    public function removeUserInterview(UserInterview $userInterview): self
    {
        if ($this->userInterviews->removeElement($userInterview) && $userInterview->getInterview() === $this) {
            // set the owning side to null (unless already changed)
            $userInterview->setInterview(null);
        }

        return $this;
    }
}
