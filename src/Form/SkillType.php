<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Skill;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkillType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) on parameter $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $nameOptions = $this->getOptions('name', [
            'attr' => ['placeholder' => 'page.framework.skill.label_name'],
        ]);

        $descriptionOptions = $this->getOptions('description', [
            'attr' => ['placeholder' => 'page.framework.skill.label_description'],
        ]);

        $priorityOptions = $this->getOptions('priority');

        $builder
            ->add('name', TextType::class, $nameOptions)
            ->add('description', TextType::class, $descriptionOptions)
            ->add('priority', HiddenType::class, $priorityOptions)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Skill::class,
        ]);
    }
}
