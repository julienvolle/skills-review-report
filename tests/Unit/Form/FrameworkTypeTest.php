<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\Framework;
use App\Form\FrameworkType;
use DateTime;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validation;

/**
 * @group unit
 */
class FrameworkTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        return [
            new PreloadedExtension([new FrameworkType()], []),
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData()
    {
        $guid = (string) Uuid::v4();
        $datetime = new DateTime();
        $formData = [
            'name'        => 'name',
            'description' => 'description',
        ];

        $framework = (new Framework())
            ->setGuid($guid)
            ->setCreatedAt($datetime)
            ->setUpdatedAt($datetime);
        $form = $this->factory->create(FrameworkType::class, $framework);

        $expected = (new Framework())
            ->setGuid($guid)
            ->setName($formData['name'])
            ->setDescription($formData['description'])
            ->setCreatedAt($datetime)
            ->setUpdatedAt($datetime);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($expected, $framework);
    }

    public function testFormView(): void
    {
        $framework = (new Framework())
            ->setGuid((string) Uuid::v4())
            ->setName('name')
            ->setDescription('description')
            ->setCreatedAt(new DateTime())
            ->setUpdatedAt(new DateTime());

        $view = $this->factory->create(FrameworkType::class, $framework)->createView();

        $this->assertSame($framework, $view->vars['value']);
    }
}
