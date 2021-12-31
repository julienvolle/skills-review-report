<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Form\ImportType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @group unit
 */
class ImportTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'file'   => new UploadedFile(__FILE__, basename(__FILE__)),
            'delete' => true,
        ];

        $form = $this->factory->create(ImportType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
    }
}
