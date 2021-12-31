<?php

declare(strict_types=1);

namespace App\Form;

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
        $fileOptions = $this->getOptions('file', [
            'label'    => 'page.import.label_file',
            'required' => true,
            'attr'     => [
                'placeholder' => 'page.import.label_dropzone',
            ],
        ]);

        $deleteOptions = $this->getOptions('delete', [
            'label'    => 'page.import.label_delete',
            'required' => false,
        ]);

        $builder
            ->add('file', DropzoneType::class, $fileOptions)
            ->add('delete', CheckboxType::class, $deleteOptions)
        ;
    }
}
