<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Common\GuidTrait;
use App\Entity\Common\NameTrait;
use App\Entity\Common\PrimaryKeyTrait;
use App\Entity\Common\PriorityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(
 *     name="level",
 *     options={
 *         "charset"="utf8mb4",
 *         "collate"="utf8mb4_unicode_ci"
 *     },
 *     indexes={
 *
 *         @ORM\Index(name="IDX_FRAMEWORK_ID_ON_LEVEL", columns={"framework_id"})
 *     },
 *     uniqueConstraints={
 *
 *         @ORM\UniqueConstraint(name="UNIQ_LEVEL_GUID", columns={"guid"})
 *     }
 * )
 */
class Level
{
    use PrimaryKeyTrait;
    use GuidTrait;
    use NameTrait;
    use PriorityTrait;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Framework",
     *     inversedBy="levels"
     * )
     *
     * @ORM\JoinColumn(name="framework_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected ?Framework $framework = null;

    public function __construct()
    {
        $this->guid = (string) Uuid::v4();
    }

    public function getFramework(): ?Framework
    {
        return $this->framework;
    }

    public function setFramework(?Framework $framework): self
    {
        $this->framework = $framework;

        return $this;
    }
}
