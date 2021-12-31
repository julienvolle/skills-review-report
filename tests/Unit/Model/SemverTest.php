<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model;

use App\Model\Semver;
use App\Tests\CustomTestCase;

/**
 * @group unit
 */
class SemverTest extends CustomTestCase
{
    private ?Semver $model = null;

    public function setUp(): void
    {
        $this->model = new Semver(1, 2, 3, 'SNAPSHOT');
    }

    public function tearDown(): void
    {
        unset($this->model);
    }

    public function testToString(): void
    {
        self::assertSame('1.2.3-SNAPSHOT', $this->model->__toString());
        self::assertSame('1.2.3-SNAPSHOT', (string) $this->model);
    }

    public function testHasMajor(): void
    {
        self::assertIsInt($this->model->getMajor());
        self::assertSame(1, $this->model->getMajor());
        self::assertSame(10, $this->model->setMajor(10)->getMajor());
    }

    public function testHasMinor(): void
    {
        self::assertIsInt($this->model->getMinor());
        self::assertSame(2, $this->model->getMinor());
        self::assertSame(20, $this->model->setMinor(20)->getMinor());
    }

    public function testHasPatch(): void
    {
        self::assertIsInt($this->model->getPatch());
        self::assertSame(3, $this->model->getPatch());
        self::assertSame(30, $this->model->setPatch(30)->getPatch());
    }

    public function testHasLabel(): void
    {
        self::assertIsString($this->model->getLabel());
        self::assertSame('SNAPSHOT', $this->model->getLabel());
        self::assertNull($this->model->setLabel(null)->getLabel());
        self::assertSame('SNAPSHOT', $this->model->setLabel('SNAPSHOT')->getLabel());
    }
}
