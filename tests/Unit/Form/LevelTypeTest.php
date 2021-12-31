<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\Level;
use App\Form\LevelType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validation;

/**
 * @group unit
 */
class LevelTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        return [
            new PreloadedExtension([new LevelType()], []),
            new ValidatorExtension($validator),
        ];
    }

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

    public function testFormView(): void
    {
        $level = (new Level())
            ->setGuid((string) Uuid::v4())
            ->setName('name');

        $view = $this->factory->create(LevelType::class, $level)->createView();

        $this->assertSame($level, $view->vars['value']);
    }
}
