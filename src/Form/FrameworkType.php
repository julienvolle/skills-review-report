<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Framework;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrameworkType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) on parameter $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'page.framework.label_name',
            ])
            ->add('description', TextType::class, [
                'required' => false,
                'label'    => 'page.framework.label_description',
            ])
            ->add('levels', CollectionType::class, [
                'entry_type'     => LevelType::class,
                'prototype_name' => '__levelName__',
                'label'          => false,
                'allow_add'      => true,
                'allow_delete'   => true,
                'by_reference'   => false,
            ])
            ->add('categories', CollectionType::class, [
                'entry_type'     => CategoryType::class,
                'prototype_name' => '__categoryName__',
                'label'          => false,
                'allow_add'      => true,
                'allow_delete'   => true,
                'by_reference'   => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Framework::class,
        ]);
    }
}
