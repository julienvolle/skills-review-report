<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\Level;
use App\Form\LevelType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @group unit
 */
class LevelTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $guid = (string) Uuid::v4();
        $formData = [
            'name'        => 'name',
            'priority'    => 1,
        ];

        $model = (new Level())->setGuid($guid);
        $form = $this->factory->create(LevelType::class, $model);

        $expected = (new Level())
            ->setGuid($guid)
            ->setName($formData['name'])
            ->setPriority($formData['priority']);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($expected, $model);
    }
}
