<?php

declare(strict_types=1);

namespace App\Entity\Common;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

trait SecuredEntityTrait
{
    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    protected bool $secured = false;

    /**
     * @ORM\Column(type="datetime", name="secured_at", nullable=true)
     */
    protected ?DateTimeInterface $securedAt = null;

    public function isSecured(): bool
    {
        return $this->secured;
    }

    public function setSecured(bool $secured): self
    {
        $this->secured = $secured;

        return $this;
    }

    public function getSecuredAt(): ?DateTimeInterface
    {
        return $this->securedAt;
    }

    public function setSecuredAt(?DateTimeInterface $securedAt): self
    {
        $this->securedAt = $securedAt;

        return $this;
    }
}
