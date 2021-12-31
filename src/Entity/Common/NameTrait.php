<?php

declare(strict_types=1);

namespace App\Entity\Common;

use App\Constant\SerializerConstant;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait NameTrait
{
    /**
     * @ORM\Column(type="string", length=100)
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_FRAMEWORK,
     *     SerializerConstant::GROUP_HASH
     * })
     */
    protected ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
