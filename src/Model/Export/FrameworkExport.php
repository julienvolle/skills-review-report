<?php

declare(strict_types=1);

namespace App\Model\Export;

use App\Constant\SerializerConstant;
use App\Entity\Framework;
use Symfony\Component\Serializer\Annotation\Groups;

final class FrameworkExport extends AbstractExport
{
    /**
     * @var Framework
     *
     * @Groups({SerializerConstant::GROUP_EXPORT_FRAMEWORK})
     */
    private $framework;

    /**
     * @return Framework|null
     */
    public function getFramework(): ?Framework
    {
        return $this->framework;
    }

    /**
     * @param Framework $framework
     *
     * @return self
     */
    public function setFramework(Framework $framework): self
    {
        $this->framework = $framework;

        return $this;
    }
}
