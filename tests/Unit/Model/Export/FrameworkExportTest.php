<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\Export;

use App\Constant\SerializerConstant;
use App\Entity\Framework;
use App\Model\Export\FrameworkExport;

/**
 * @group unit
 */
class FrameworkExportTest extends AbstractExportTest
{
    public function setUp(): void
    {
        $this->model = new FrameworkExport();

        parent::setUp();
    }

    public function testHasFramework(): void
    {
        self::assertNull($this->model->getFramework());
        self::assertInstanceOf(Framework::class, $this->model->setFramework(new Framework())->getFramework());
    }

    /** @dataProvider providerTestGroupsSerialiser */
    public function testGroupsSerialiser(array $groups, string $expected): void
    {
        self::assertSame($expected, $this->serializer->serialize(
            $this->model->setAppVersion('1.2.3'),
            SerializerConstant::FORMAT_EXPORT,
            ['groups' => $groups]
        ));
    }

    public function providerTestGroupsSerialiser(): iterable
    {
        yield 'group_' . SerializerConstant::GROUP_EXPORT => [[
            SerializerConstant::GROUP_EXPORT,
        ], '{"appVersion":"1.2.3","exportedAt":null}'];

        yield 'group_' . SerializerConstant::GROUP_EXPORT_FRAMEWORK => [[
            SerializerConstant::GROUP_EXPORT_FRAMEWORK,
        ], '{"framework":null}'];

        yield 'groups' => [[
            SerializerConstant::GROUP_EXPORT,
            SerializerConstant::GROUP_EXPORT_FRAMEWORK,
        ], '{"framework":null,"appVersion":"1.2.3","exportedAt":null}'];
    }
}
