<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
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
        $nameOptions = $this->getOptions('name', [
            'attr' => ['placeholder' => 'page.framework.category.label_name'],
        ]);

        $descriptionOptions = $this->getOptions('description', [
            'attr' => ['placeholder' => 'page.framework.category.label_description'],
        ]);

        $priorityOptions = $this->getOptions('priority');

        $skillsOptions = $this->getOptions('skills', [
            'entry_type'     => SkillType::class,
            'prototype_name' => '__skillName__',
            'label'          => false,
            'allow_add'      => true,
            'allow_delete'   => true,
            'by_reference'   => false,
        ]);

        $builder
            ->add('name', TextType::class, $nameOptions)
            ->add('description', TextType::class, $descriptionOptions)
            ->add('priority', HiddenType::class, $priorityOptions)
            ->add('skills', CollectionType::class, $skillsOptions)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
