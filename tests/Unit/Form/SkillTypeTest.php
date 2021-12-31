<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\Skill;
use App\Form\SkillType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validation;

/**
 * @group unit
 */
class SkillTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        return [
            new PreloadedExtension([new SkillType()], []),
            new ValidatorExtension($validator),
        ];
    }

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

    public function testFormView(): void
    {
        $skill = (new Skill())
            ->setGuid((string) Uuid::v4())
            ->setName('name')
            ->setDescription('description');

        $view = $this->factory->create(SkillType::class, $skill)->createView();

        $this->assertSame($skill, $view->vars['value']);
    }
}
