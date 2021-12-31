<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\Export;

use App\Model\Export\AbstractExport;
use App\Tests\CustomTestCase;
use DateTime;
use DateTimeInterface;
use Symfony\Component\Serializer\Serializer;

abstract class AbstractExportTest extends CustomTestCase
{
    protected ?AbstractExport $model = null;
    protected ?Serializer $serializer = null;

    public function setUp(): void
    {
        self::bootKernel();
        $this->serializer = static::getContainer()->get('serializer');
    }

    public function tearDown(): void
    {
        unset($this->model);
        unset($this->serializer);
    }

    public function testHasAppVersion(): void
    {
        self::assertNull($this->model->getAppVersion());
        self::assertIsString($this->model->setAppVersion('1.2.3')->getAppVersion());
    }

    public function testHasExportedAt(): void
    {
        self::assertNull($this->model->getExportedAt());
        self::assertInstanceOf(DateTimeInterface::class, $this->model->setExportedAt(new DateTime())->getExportedAt());
    }
}
