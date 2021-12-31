<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Constant\SecurityConstant;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validation;

/**
 * @group unit
 */
class UserTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(true)
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();

        return [
            new PreloadedExtension([new UserType()], []),
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData()
    {
        $guid = (string) Uuid::v4();
        $formData = [
            'email'         => 'sample@domain.com',
            'plainPassword' => 'sample',
            'role'          => SecurityConstant::ROLE_ADMIN,
        ];

        $model = (new User())->setGuid($guid);
        $form = $this->factory->create(UserType::class, $model);

        $expected = (new User())->setGuid($guid)->setEmail($formData['email']);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($expected, $model);
    }

    public function testFormView(): void
    {
        $user = (new User())
            ->setGuid((string) Uuid::v4())
            ->setEmail('email');

        $view = $this->factory->create(UserType::class, $user)->createView();

        $this->assertSame($user, $view->vars['value']);
    }
}
