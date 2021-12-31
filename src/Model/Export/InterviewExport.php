<?php

declare(strict_types=1);

namespace App\Model\Export;

use App\Constant\SerializerConstant;
use App\Entity\Interview;
use Symfony\Component\Serializer\Annotation\Groups;

final class InterviewExport extends AbstractExport
{
    /**
     * @Groups({SerializerConstant::GROUP_EXPORT_INTERVIEW})
     */
    private ?Interview $interview = null;

    public function getInterview(): ?Interview
    {
        return $this->interview;
    }

    public function setInterview(Interview $interview): self
    {
        $this->interview = $interview;

        return $this;
    }
}
