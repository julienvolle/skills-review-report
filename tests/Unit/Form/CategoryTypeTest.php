<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @group unit
 */
class CategoryTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $guid = (string) Uuid::v4();
        $formData = [
            'name'        => 'name',
            'description' => 'description',
            'priority'    => 1,
        ];

        $model = (new Category())->setGuid($guid);
        $form = $this->factory->create(CategoryType::class, $model);

        $expected = (new Category())
            ->setGuid($guid)
            ->setName($formData['name'])
            ->setDescription($formData['description'])
            ->setPriority($formData['priority']);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($expected, $model);
    }
}
