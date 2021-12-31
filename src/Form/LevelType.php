<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Level;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LevelType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) on parameter $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $nameOptions = $this->getOptions('name', [
            'attr' => ['placeholder' => 'page.framework.level.label_name'],
        ]);

        $priorityOptions = $this->getOptions('priority');

        $builder
            ->add('name', TextType::class, $nameOptions)
            ->add('priority', HiddenType::class, $priorityOptions)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Level::class,
        ]);
    }
}
