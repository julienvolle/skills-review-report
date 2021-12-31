<?php

declare(strict_types=1);

namespace App\Tests\Unit\Twig;

use App\Constant\TranslationConstant;
use App\Service\ColorService;
use App\Tests\CustomTestCase;
use App\Twig\TwigExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @group unit
 */
class TwigExtensionTest extends CustomTestCase
{
    public function testGetFilters(): void
    {
        $filters = (new TwigExtension())->getFilters();

        self::assertIsArray($filters);
        self::assertContainsOnlyInstancesOf(TwigFilter::class, $filters);

        foreach ($filters as $filter) {
            if ($filter->getName() === 'sum') {
                self::assertSame('\array_sum', $filter->getCallable());
            }
        }
    }

    public function testGetFunctions(): void
    {
        $functions = (new TwigExtension())->getFunctions();

        self::assertIsArray($functions);
        self::assertContainsOnlyInstancesOf(TwigFunction::class, $functions);

        foreach ($functions as $function) {
            if ($function->getName() === 'getGradientColor') {
                self::assertIsArray($function->getCallable());
                self::assertInstanceOf(ColorService::class, $function->getCallable()[0]);
                self::assertSame('getGradientColor', $function->getCallable()[1]);
            }
            if ($function->getName() === 'getLanguages') {
                self::assertIsArray($function->getCallable()());
                self::assertSame(TranslationConstant::LANGUAGES, $function->getCallable()());
            }
        }
    }
}
