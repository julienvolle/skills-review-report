<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\Skill;
use App\Form\SkillType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @group unit
 */
class SkillTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $guid = (string) Uuid::v4();
        $formData = [
            'name'        => 'name',
            'description' => 'description',
            'priority'    => 1,
        ];

        $model = (new Skill())->setGuid($guid);
        $form = $this->factory->create(SkillType::class, $model);

        $expected = (new Skill())
            ->setGuid($guid)
            ->setName($formData['name'])
            ->setDescription($formData['description'])
            ->setPriority($formData['priority']);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($expected, $model);
    }
}
