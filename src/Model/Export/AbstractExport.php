<?php

declare(strict_types=1);

namespace App\Model\Export;

use App\Constant\SerializerConstant;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Groups;

abstract class AbstractExport
{
    /**
     * @var string
     *
     * @Groups({SerializerConstant::GROUP_EXPORT})
     */
    private $appVersion;

    /**
     * @var DateTimeInterface
     *
     * @Groups({SerializerConstant::GROUP_EXPORT})
     */
    private $exportedAt;

    /**
     * @return string|null
     */
    public function getAppVersion(): ?string
    {
        return $this->appVersion;
    }

    /**
     * @param string $appVersion
     *
     * @return self
     */
    public function setAppVersion(string $appVersion): self
    {
        $this->appVersion = $appVersion;

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getExportedAt(): ?DateTimeInterface
    {
        return $this->exportedAt;
    }

    /**
     * @param DateTimeInterface $exportedAt
     *
     * @return self
     */
    public function setExportedAt(DateTimeInterface $exportedAt): self
    {
        $this->exportedAt = $exportedAt;

        return $this;
    }
}
