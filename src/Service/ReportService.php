<?php

declare(strict_types=1);

namespace App\Service;

use App\Constant\ColorConstant;
use App\Entity\Framework;
use App\Entity\Interview;
use InvalidArgumentException;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ReportService
{
    private $chartBuilder;

    public function __construct(ChartBuilderInterface $chartBuilder)
    {
        $this->chartBuilder = $chartBuilder;
    }

    /**
     * Get data to display an interview report
     *
     * @throws InvalidArgumentException
     */
    public function getReportData(Interview $interview): array
    {
        // Set report data
        $labels = $scores = [];
        /** @var Framework $framework */
        $framework = $interview->getFramework();
        foreach ($framework->getCategories() as $category) {
            $labels[] = $category->getName();
            foreach ($category->getSkills() as $skill) {
                $score = floatval($interview->getResult()[$skill->getGuid()] ?? 1);
                $scores[$category->getGuid()][$skill->getGuid()] = $score;
            }
        }

        // Set default level required
        if (empty($interview->getResult()['level_required'])) {
            $interview->setResult(\array_merge($interview->getResult(), ['level_required' => 1]));
        }

        // Set chart data
        $label = 'Compétences';
        $data = \array_map(static function ($ask) use ($interview) {
            $max = \max($interview->getFramework()->getLevels()->count() - 1, 1);
            $moy = \array_sum($ask) / \max(\count($ask), 1);
            $percent = ($moy - 1) * 100 / $max;
            return \round($percent, 2);
        }, $scores);

        // Set chart colors
        $backgroundColor           = 'rgba(' . \implode(',', ColorService::toDecColor(ColorConstant::PINK)) . ',0.25)';
        $borderColor               = 'rgb(' . \implode(',', ColorService::toDecColor(ColorConstant::PINK)) . ')';
        $pointBackgroundColor      = 'rgb(' . \implode(',', ColorService::toDecColor(ColorConstant::GREY)) . ')';
        $pointBorderColor          = 'rgb(' . \implode(',', ColorService::toDecColor(ColorConstant::WHITE)) . ')';
        $pointHoverBackgroundColor = 'rgb(' . \implode(',', ColorService::toDecColor(ColorConstant::WHITE)) . ')';
        $pointHoverBorderColor     = 'rgb(' . \implode(',', ColorService::toDecColor(ColorConstant::GREY)) . ')';

        // Build chart
        $radar = $this->chartBuilder->createChart(Chart::TYPE_RADAR);
        $radar->setData([
            'labels'   => $labels,
            'datasets' => [
                [
                    'label'                     => $label,
                    'backgroundColor'           => $backgroundColor,
                    'borderColor'               => $borderColor,
                    'pointBackgroundColor'      => $pointBackgroundColor,
                    'pointBorderColor'          => $pointBorderColor,
                    'pointHoverBackgroundColor' => $pointHoverBackgroundColor,
                    'pointHoverBorderColor'     => $pointHoverBorderColor,
                    'data'                      => \array_values($data),
                ],
            ],
        ]);
        $radar->setOptions([
            'responsive'  => true,
            'aspectRatio' => 2,
            'scale' => [
                'r' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                    'angleLines'   => ['display' => false],
                    'ticks'        => ['min' => 0, 'max' => 100, 'stepSize' => 10],
                ],
            ],
        ]);

        return [
            'interview' => $interview,
            'scores'    => $scores,
            'chart'     => [
                'radar' => $radar,
            ],
        ];
    }
}
