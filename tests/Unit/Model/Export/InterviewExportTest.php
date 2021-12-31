<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\Export;

use App\Constant\SerializerConstant;
use App\Entity\Interview;
use App\Model\Export\InterviewExport;

/**
 * @group unit
 */
class InterviewExportTest extends AbstractExportTest
{
    public function setUp(): void
    {
        $this->model = new InterviewExport();

        parent::setUp();
    }

    public function testHasInterview(): void
    {
        self::assertNull($this->model->getInterview());
        self::assertInstanceOf(Interview::class, $this->model->setInterview(new Interview())->getInterview());
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

        yield 'group_' . SerializerConstant::GROUP_EXPORT_INTERVIEW => [[
            SerializerConstant::GROUP_EXPORT_INTERVIEW,
        ], '{"interview":null}'];

        yield 'groups' => [[
            SerializerConstant::GROUP_EXPORT,
            SerializerConstant::GROUP_EXPORT_INTERVIEW,
        ], '{"interview":null,"appVersion":"1.2.3","exportedAt":null}'];
    }
}
