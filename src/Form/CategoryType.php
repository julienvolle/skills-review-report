<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) on parameter $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'attr'     => ['placeholder' => 'page.framework.category.label_name'],
            ])
            ->add('description', TextType::class, [
                'required' => false,
                'attr'     => ['placeholder' => 'page.framework.category.label_description'],
            ])
            ->add('priority', HiddenType::class, [
                'required' => true,
                'attr'     => ['data-sortable' => 'priority'],
            ])
            ->add('skills', CollectionType::class, [
                'entry_type'     => SkillType::class,
                'prototype_name' => '__skillName__',
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
            'data_class' => Category::class,
        ]);
    }
}
