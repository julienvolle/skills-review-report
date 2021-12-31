<?php

declare(strict_types=1);

namespace App\Form;

use App\Constant\SecurityConstant;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) on parameter $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label'    => 'page.register.label_email',
                'attr'     => ['placeholder' => 'page.register.label_email'],
            ])
            ->add('plainPassword', PasswordType::class, [
                'required' => true,
                'label'    => 'page.register.label_password',
                'attr'     => ['placeholder' => 'page.register.label_password'],
                'mapped'   => false,
            ])
            ->add('role', ChoiceType::class, [
                'required' => true,
                'label'    => 'page.register.label_role',
                'attr'     => ['placeholder' => 'page.register.label_role'],
                'mapped'   => false,
                'choices'  => [
                    'page.register.roles.user'  => SecurityConstant::ROLE_USER,
                    'page.register.roles.admin' => SecurityConstant::ROLE_ADMIN,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
