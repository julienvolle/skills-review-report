<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Interview;
use App\Form\DataTransformer\NumberDataTransformer;
use DateTime;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class InterviewType extends AbstractType
{
    private const PROPERTY_PATH_RESULT = 'result';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) on parameter $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Interview $interview */
        $interview = $options['data'];

        $titleOptions = $this->getOptions('title', [
            'required' => true,
            'data'     => $interview->getTitle(),
            'label'    => 'page.interview.label_title',
            'constraints' => [new Length([
                'min'        => 1,
                'max'        => 500,
                'minMessage' => 'field.title.length.min',
                'maxMessage' => 'field.title.length.max',
            ])],
        ]);

        $firstnameOptions = $this->getOptions('firstname', [
            'required'    => true,
            'data'        => $interview->getFirstname(),
            'label'       => 'page.interview.label_firstname',
            'constraints' => [new Length([
                'min'        => 1,
                'max'        => 250, // max decrypted length
                'minMessage' => 'field.firstname.length.min',
                'maxMessage' => 'field.firstname.length.max',
            ])],
        ]);

        $lastnameOptions = $this->getOptions('lastname', [
            'required'    => true,
            'data'        => $interview->getLastname(),
            'label'       => 'page.interview.label_lastname',
            'constraints' => [new Length([
                'min'        => 1,
                'max'        => 250, // max decrypted length
                'minMessage' => 'field.lastname.length.min',
                'maxMessage' => 'field.lastname.length.max',
            ])],
        ]);

        $createdAtOptions = $this->getOptions('createdAt', [
            'required' => true,
            'data'     => $interview->getCreatedAt() ?? (new DateTime()),
            'label'    => 'page.interview.label_date',
            'html5'    => false,
            'widget'   => 'single_text',
            'format'   => 'dd/MM/yyyy HH:00',
        ]);

        $builder
            ->add('title', TextType::class, $titleOptions)
            ->add('firstname', TextType::class, $firstnameOptions)
            ->add('lastname', TextType::class, $lastnameOptions)
            ->add('createdAt', DateTimeType::class, $createdAtOptions)
        ;

        $this->addResultFields($builder, $interview);

        $guid = $interview->getFramework()->getGuid();
        $guidOptions = $this->getOptions($guid, [
            'required'      => false,
            'label'         => 'page.interview.label_conclusion',
            'data'          => $interview->getResult()[$guid] ?? null,
            'property_path' => self::PROPERTY_PATH_RESULT . '[' . $guid . ']',
            'attr'          => ['placeholder' => 'page.interview.placeholder_conclusion'],
        ]);

        $builder->add($interview->getFramework()->getGuid(), TextareaType::class, $guidOptions);
    }

    private function addResultFields(FormBuilderInterface $builder, Interview $interview): void
    {
        $result = $interview->getResult();

        $levels = $interview->getFramework()->getLevels()->toArray();
        $choices = \array_combine(
            \array_map(static function ($level) {
                return $level->getName();
            }, $levels),
            \array_map(static function ($level) use ($levels) {
                return (\count($levels) - $level->getPriority()) + 1;
            }, $levels)
        );
        $mixedLevels = [];
        foreach ($levels as $index => $level) {
            $levelUp = $level->getName();
            if (isset($levels[$index + 1]) && $choices[$levelUp] > 2) {
                $levelDown = $levels[$index + 1]->getName();
                $mixedLevels[$levelDown . ' / ' . $levelUp] = \round($choices[$levelUp] - 0.5, 1);
            }
        }
        $choices = \array_merge($choices, $mixedLevels);
        \arsort($choices);
        \array_pop($choices); // Remove last level (never required in interview)
        $builder->add('level_required', ChoiceType::class, [
            'required'                  => true,
            'label'                     => 'page.interview.label_level_required',
            'data'                      => $result['level_required'] ?? null,
            'property_path'             => self::PROPERTY_PATH_RESULT . '[level_required]',
            'choices'                   => $choices,
            'choice_translation_domain' => false,
        ]);

        $defaultOptions = [
            'attr' => [
                'min'  => 1,
                'max'  => \count($levels),
                'step' => 0.5,
            ],
        ];
        foreach ($interview->getFramework()->getCategories() as $category) {
            $builder->add($category->getGuid(), TextareaType::class, [
                'required'      => false,
                'data'          => $result[$category->getGuid()] ?? null,
                'property_path' => self::PROPERTY_PATH_RESULT . '[' . $category->getGuid() . ']',
                'attr'          => ['placeholder' => 'page.interview.placeholder_comment'],
            ]);
            foreach ($category->getSkills() as $skill) {
                $options = \array_merge($defaultOptions, [
                    'label'         => $skill->getName(),
                    'data'          => $result[$skill->getGuid()] ?? $defaultOptions['attr']['min'],
                    'property_path' => self::PROPERTY_PATH_RESULT . '[' . $skill->getGuid() . ']',
                ]);
                $builder->add($skill->getGuid(), RangeType::class, \array_merge($defaultOptions, $options));
                $builder->get($skill->getGuid())->addModelTransformer((new NumberDataTransformer()));
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Interview::class,
        ]);
    }
}
