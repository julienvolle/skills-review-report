<?php

declare(strict_types=1);

namespace App\Twig;

use App\Constant\TranslationConstant;
use App\Service\ColorService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('sum', '\array_sum'),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getGradientColor', [(new ColorService()), 'getGradientColor']),
            new TwigFunction('getLanguages', function (): array {
                return TranslationConstant::LANGUAGES;
            }),
        ];
    }
}
