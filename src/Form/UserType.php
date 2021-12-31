<?php

declare(strict_types=1);

namespace App\Form;

use App\Constant\SecurityConstant;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) on parameter $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $emailOptions = $this->getOptions('email', [
            'required'    => true,
            'label'       => 'page.register.label_email',
            'attr'        => ['placeholder' => 'page.register.label_email'],
            'constraints' => [
                new Length([
                    'min'        => 5,
                    'max'        => 500,
                    'minMessage' => 'field.email.length.min',
                    'maxMessage' => 'field.email.length.max',
                ]),
                new Email(),
            ],
        ]);

        $plainPasswordOptions = $this->getOptions('plainPassword', [
            'required' => true,
            'label'    => 'page.register.label_password',
            'attr'     => ['placeholder' => 'page.register.label_password'],
            'mapped'   => false,
        ]);

        $roleOptions = $this->getOptions('role', [
            'required' => true,
            'label'    => 'page.register.label_role',
            'attr'     => ['placeholder' => 'page.register.label_role'],
            'mapped'   => false,
            'choices'  => [
                'page.register.roles.user'  => SecurityConstant::ROLE_USER,
                'page.register.roles.admin' => SecurityConstant::ROLE_ADMIN,
            ],
        ]);

        $builder
            ->add('email', EmailType::class, $emailOptions)
            ->add('plainPassword', PasswordType::class, $plainPasswordOptions)
            ->add('role', ChoiceType::class, $roleOptions)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
