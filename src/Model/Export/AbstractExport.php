<?php

declare(strict_types=1);

namespace App\Model\Export;

use App\Constant\SerializerConstant;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Groups;

abstract class AbstractExport
{
    /**
     * @Groups({SerializerConstant::GROUP_EXPORT})
     */
    private ?string $appVersion = null;

    /**
     * @Groups({SerializerConstant::GROUP_EXPORT})
     */
    private ?DateTimeInterface $exportedAt = null;

    public function getAppVersion(): ?string
    {
        return $this->appVersion;
    }

    public function setAppVersion(string $appVersion): self
    {
        $this->appVersion = $appVersion;

        return $this;
    }

    public function getExportedAt(): ?DateTimeInterface
    {
        return $this->exportedAt;
    }

    public function setExportedAt(DateTimeInterface $exportedAt): self
    {
        $this->exportedAt = $exportedAt;

        return $this;
    }
}
