<?php

declare(strict_types=1);

namespace App\Entity\Common;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait TimestampableTrait
{
    /**
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_FRAMEWORK,
     *     SerializerConstant::GROUP_EXPORT_INTERVIEW
     * })
     */
    protected ?DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_FRAMEWORK,
     *     SerializerConstant::GROUP_EXPORT_INTERVIEW
     * })
     */
    protected ?DateTimeInterface $updatedAt = null;

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }
}
