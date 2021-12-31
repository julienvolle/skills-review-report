<?php

declare(strict_types=1);

namespace App\Entity\Common;

use App\Constant\SerializerConstant;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait PriorityTrait
{
    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({
     *     SerializerConstant::GROUP_EXPORT_FRAMEWORK,
     *     SerializerConstant::GROUP_HASH
     * })
     */
    protected int $priority = 1;

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
