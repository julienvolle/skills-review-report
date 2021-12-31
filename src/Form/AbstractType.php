<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Validator\Constraints\Length;

abstract class AbstractType extends \Symfony\Component\Form\AbstractType
{
    protected function getOptions(string $child, array $additionalOptions = []): array
    {
        switch ($child) {
            case 'name':
                $options = [
                    'required'    => true,
                    'constraints' => [new Length([
                        'min'        => 1,
                        'max'        => 100,
                        'minMessage' => 'field.name.length.min',
                        'maxMessage' => 'field.name.length.max',
                    ])],
                ];
                break;
            case 'description':
                $options = [
                    'required'    => false,
                    'constraints' => [new Length([
                        'max'        => 500,
                        'maxMessage' => 'field.description.length.max',
                    ])],
                ];
                break;
            case 'priority':
                $options = [
                    'required' => true,
                    'attr'     => ['data-sortable' => 'priority'],
                ];
                break;
            default:
                $options = []; // no common options
        }

        return \array_replace_recursive($options, $additionalOptions);
    }
}
