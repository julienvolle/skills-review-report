<?php

declare(strict_types=1);

namespace App\Entity\Common;

use App\Constant\SerializerConstant;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait DescriptionTrait
{
    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_FRAMEWORK,
     *     SerializerConstant::GROUP_HASH
     * })
     */
    protected ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
