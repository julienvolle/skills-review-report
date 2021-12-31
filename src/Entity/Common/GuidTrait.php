<?php

declare(strict_types=1);

namespace App\Entity\Common;

use App\Constant\SerializerConstant;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait GuidTrait
{
    /**
     * @ORM\Column(type="guid", unique=true)
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_FRAMEWORK,
     *     SerializerConstant::GROUP_EXPORT_INTERVIEW,
     *     SerializerConstant::GROUP_HASH
     * })
     */
    protected ?string $guid = null;

    public function getGuid(): ?string
    {
        return $this->guid;
    }

    public function setGuid(string $guid): self
    {
        $this->guid = $guid;

        return $this;
    }
}
