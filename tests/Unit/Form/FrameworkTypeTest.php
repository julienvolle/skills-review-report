<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\Framework;
use App\Form\FrameworkType;
use DateTime;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @group unit
 */
class FrameworkTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $guid = (string) Uuid::v4();
        $datetime = new DateTime();
        $formData = [
            'name'        => 'name',
            'description' => 'description',
        ];

        $model = (new Framework())
            ->setGuid($guid)
            ->setCreatedAt($datetime)
            ->setUpdatedAt($datetime);
        $form = $this->factory->create(FrameworkType::class, $model);

        $expected = (new Framework())
            ->setGuid($guid)
            ->setName($formData['name'])
            ->setDescription($formData['description'])
            ->setCreatedAt($datetime)
            ->setUpdatedAt($datetime);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($expected, $model);
    }
}
