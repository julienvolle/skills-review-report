<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Category;
use App\Entity\Framework;
use App\Entity\Interview;
use App\Entity\Level;
use App\Entity\Skill;
use App\Service\ReportService;
use App\Tests\CustomTestCase;
use DateTime;
use Prophecy\Argument;
use Symfony\Component\Uid\Uuid;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

/**
 * @group unit
 */
class ReportServiceTest extends CustomTestCase
{
    public function setUp(): void
    {
        $this->setProphecies([
            ChartBuilderInterface::class,
            Chart::class,
        ]);
    }

    public function testGetReportData(): void
    {
        $datetime = new DateTime();
        $guid = [
            Framework::class => (string) Uuid::v4(),
            Level::class     => (string) Uuid::v4(),
            Category::class  => (string) Uuid::v4(),
            Skill::class     => (string) Uuid::v4(),
            Interview::class => (string) Uuid::v4(),
        ];

        $interview = (new Interview())
            ->setGuid($guid[Interview::class])
            ->setCreatedAt($datetime)
            ->setUpdatedAt($datetime)
            ->setResult([$guid[Skill::class] => 2.0])
            ->setFramework((new Framework())->setGuid($guid[Framework::class])
                ->addLevel((new Level())->setGuid($guid[Level::class] . '_1')->setName('level_1'))
                ->addLevel((new Level())->setGuid($guid[Level::class] . '_2')->setName('level_2'))
                ->addLevel((new Level())->setGuid($guid[Level::class] . '_3')->setName('level_3'))
                ->addCategory((new Category())->setGuid($guid[Category::class])->setName('category_1')
                    ->addSkill((new Skill())->setGuid($guid[Skill::class])->setName('skill_1'))))
        ;

        $this->getProphecy(ChartBuilderInterface::class)
            ->createChart(Argument::exact(Chart::TYPE_RADAR))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Chart::class));

        $this->getProphecy(Chart::class)
            ->setData(Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Chart::class));

        $this->getProphecy(Chart::class)
            ->setOptions(Argument::type('array'))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Chart::class));

        $reportService = new ReportService($this->getReveal(ChartBuilderInterface::class));
        $data = $reportService->getReportData($interview);
        self::assertIsArray($data);
        self::assertArrayHasKey('interview', $data);
        self::assertSame($interview, $data['interview']);
        self::assertArrayHasKey('scores', $data);
        self::assertSame([$guid[Category::class] => [$guid[Skill::class] => 2.0]], $data['scores']);
        self::assertIsArray($data['scores']);
        self::assertArrayHasKey('chart', $data);
        self::assertArrayHasKey('radar', $data['chart']);
        self::assertSame($this->getReveal(Chart::class), $data['chart']['radar']);
    }
}
