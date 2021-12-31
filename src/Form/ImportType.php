<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\UX\Dropzone\Form\DropzoneType;

class ImportType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) on parameter $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', DropzoneType::class, [
                'label'    => 'page.import.label_file',
                'required' => true,
                'attr'     => [
                    'placeholder' => 'page.import.label_dropzone',
                ],
            ])
            ->add('delete', CheckboxType::class, [
                'label'    => 'page.import.label_delete',
                'required' => false,
            ])
        ;
    }
}
