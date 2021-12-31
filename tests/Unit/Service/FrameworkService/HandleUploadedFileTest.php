<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FrameworkService;

use App\Constant\SerializerConstant;
use App\Entity\Framework;
use App\Exception\Framework\FrameworkImportException;
use App\Model\Export\FrameworkExport;
use Prophecy\Argument;
use stdClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group unit
 */
class HandleUploadedFileTest extends AbstractTestFrameworkService
{
    /** @dataProvider providerTestHandleUploadedFile */
    public function testHandleUploadedFile($extension, $mimeType, $errorMessage, $framework): void
    {
        $file = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $file->method('getClientOriginalExtension')->willReturn($extension);
        $file->method('getMimeType')->willReturn($mimeType);

        if ($errorMessage) {
            $this->getProphecy(TranslatorInterface::class)
                ->trans(
                    Argument::exact($errorMessage),
                    Argument::type('array'),
                    Argument::exact('errors')
                )
                ->shouldBeCalledOnce()
                ->willReturn('error_message_translated');
            $this->expectException(FrameworkImportException::class);
            $this->expectExceptionMessage('error_message_translated');
        } else {
            $this->getProphecy(SerializerInterface::class)
                ->deserialize(
                    Argument::type('string'),
                    Argument::exact(FrameworkExport::class),
                    Argument::exact(SerializerConstant::FORMAT_EXPORT)
                )
                ->shouldBeCalled()
                ->willReturn($framework);
        }

        $this->frameworkService->handleUploadedFile($file);
    }

    public function providerTestHandleUploadedFile(): iterable
    {
        yield 'wrong_extension' => [
            'xml',
            'application/xml',
            'exception.framework.import.upload_file.extension',
            null,
        ];

        yield 'wrong_mime_type' => [
            'json',
            'application/xml',
            'exception.framework.import.upload_file.type',
            null,
        ];

        yield 'wrong_instanceof' => [
            'json',
            'application/json',
            'exception.framework.import.upload_file.invalid',
            new stdClass(),
        ];

        yield 'framework_data_lost' => [
            'json',
            'application/json',
            'exception.framework.import.upload_file.invalid',
            new FrameworkExport(),
        ];

        yield 'handle_success' => [
            'json',
            'application/json',
            null,
            (new FrameworkExport())->setFramework(new Framework()),
        ];
    }
}
