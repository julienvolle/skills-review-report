<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Interview;
use App\Form\DataTransformer\NumberDataTransformer;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterviewType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) on parameter $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Interview $interview */
        $interview = $options['data'];

        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'data'     => $interview->getTitle(),
                'label'    => 'page.interview.label_title',
            ])
            ->add('firstname', TextType::class, [
                'required' => true,
                'data'     => $interview->getFirstname(),
                'label'    => 'page.interview.label_firstname',
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
                'data'     => $interview->getLastname(),
                'label'    => 'page.interview.label_lastname',
            ])
            ->add('createdAt', DateTimeType::class, [
                'label'    => 'page.interview.label_date',
                'required' => true,
                'html5'    => false,
                'widget'   => 'single_text',
                'format'   => 'dd/MM/yyyy HH:00',
                'data'     => $interview->getCreatedAt() ?? (new DateTime('NOW')),
            ])
        ;

        $result = $interview->getResult();

        $levels = $interview->getFramework()->getLevels()->toArray();
        $choices = array_combine(
            array_map(function ($level) {
                return $level->getName();
            }, $levels),
            array_map(function ($level) use ($levels) {
                return (count($levels) - $level->getPriority()) + 1;
            }, $levels)
        );
        $mixedLevels = [];
        foreach ($levels as $index => $level) {
            $levelUp = $level->getName();
            if (isset($levels[$index + 1]) && $choices[$levelUp] > 2) {
                $levelDown = $levels[$index + 1]->getName();
                $mixedLevels[$levelDown . ' / ' . $levelUp] = round($choices[$levelUp] - 0.5, 1);
            }
        }
        $choices = array_merge($choices, $mixedLevels);
        arsort($choices);
        array_pop($choices); // Remove last level (never required in interview)
        $builder->add('level_required', ChoiceType::class, [
            'label'                     => 'page.interview.label_level_required',
            'required'                  => true,
            'choices'                   => $choices,
            'property_path'             => 'result[level_required]',
            'data'                      => $result['level_required'] ?? null,
            'choice_translation_domain' => false,
        ]);

        $options = [
            'attr' => [
                'min'  => 1,
                'max'  => count($levels),
                'step' => 0.5,
            ],
        ];
        foreach ($interview->getFramework()->getCategories() as $category) {
            $builder->add($category->getGuid(), TextareaType::class, [
                'required'      => false,
                'property_path' => 'result[' . $category->getGuid() . ']',
                'data'          => $result[$category->getGuid()] ?? null,
                'attr'          => ['placeholder' => 'page.interview.placeholder_comment'],
            ]);
            foreach ($category->getSkills() as $skill) {
                $builder->add($skill->getGuid(), RangeType::class, array_merge($options, [
                    'label'         => $skill->getName(),
                    'property_path' => 'result[' . $skill->getGuid() . ']',
                    'data'          => $result[$skill->getGuid()] ?? $options['attr']['min'],
                ]));
                $builder->get($skill->getGuid())->addModelTransformer((new NumberDataTransformer()));
            }
        }

        $builder->add($interview->getFramework()->getGuid(), TextareaType::class, [
            'label'         => 'page.interview.label_conclusion',
            'required'      => false,
            'property_path' => 'result[' . $interview->getFramework()->getGuid() . ']',
            'data'          => $result[$interview->getFramework()->getGuid()] ?? null,
            'attr'          => ['placeholder' => 'page.interview.placeholder_conclusion'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Interview::class,
        ]);
    }
}
