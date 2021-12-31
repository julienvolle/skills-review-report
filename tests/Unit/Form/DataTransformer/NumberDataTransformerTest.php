<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\DataTransformer;

use App\Form\DataTransformer\NumberDataTransformer;
use App\Tests\CustomTestCase;

/**
 * @group unit
 */
class NumberDataTransformerTest extends CustomTestCase
{
    /** @dataProvider providerTestTransform */
    public function testTransform($input, $output): void
    {
        self::assertSame($output, (new NumberDataTransformer())->transform($input));
    }

    public function providerTestTransform(): iterable
    {
        yield '0'    => [ 0,    '0'    ];
        yield '1'    => [ 1,    '1'    ];
        yield '0.0'  => [ 0.0,  '0'    ];
        yield '1.0'  => [ 1.0,  '1'    ];
        yield '0.1'  => [ 0.1,  '0.1'  ];
        yield '0.01' => [ 0.01, '0.01' ];
        yield '1.01' => [ 1.01, '1.01' ];
        yield '-0'   => [ -0,   '0'    ];
        yield '-1'   => [ -1,   '-1'   ];
        yield '-0.1' => [ -0.1, '-0.1' ];
    }

    /** @dataProvider providerTestReverseTransform */
    public function testReverseTransform($input, $output): void
    {
        self::assertSame($output, (new NumberDataTransformer())->reverseTransform($input));
    }

    public function providerTestReverseTransform(): iterable
    {
        yield '0'    => [ '0',    0.0  ];
        yield '1'    => [ '1',    1.0  ];
        yield '0.0'  => [ '0.0',  0.0  ];
        yield '1.0'  => [ '1.0',  1.0  ];
        yield '0.1'  => [ '0.1',  0.1  ];
        yield '0.01' => [ '0.01', 0.01 ];
        yield '1.01' => [ '1.01', 1.01 ];
        yield '-0'   => [ '-0',   0.0  ];
        yield '-1'   => [ '-1',   -1.0 ];
        yield '-0.1' => [ '-0.1', -0.1 ];
    }
}
