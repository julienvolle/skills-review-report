<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Framework;
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
        $nameOptions = $this->getOptions('name', [
            'label' => 'page.framework.label_name',
            'attr'  => ['placeholder' => 'page.framework.label_name'],
        ]);

        $descriptionOptions = $this->getOptions('description', [
            'label' => 'page.framework.label_description',
            'attr'  => ['placeholder' => 'page.framework.label_description'],
        ]);

        $levelsOptions = $this->getOptions('levels', [
            'entry_type'     => LevelType::class,
            'prototype_name' => '__levelName__',
            'label'          => false,
            'allow_add'      => true,
            'allow_delete'   => true,
            'by_reference'   => false,
        ]);

        $categoriesOptions = $this->getOptions('categories', [
            'entry_type'     => CategoryType::class,
            'prototype_name' => '__categoryName__',
            'label'          => false,
            'allow_add'      => true,
            'allow_delete'   => true,
            'by_reference'   => false,
        ]);

        $builder
            ->add('name', TextType::class, $nameOptions)
            ->add('description', TextType::class, $descriptionOptions)
            ->add('levels', CollectionType::class, $levelsOptions)
            ->add('categories', CollectionType::class, $categoriesOptions)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Framework::class,
        ]);
    }
}
