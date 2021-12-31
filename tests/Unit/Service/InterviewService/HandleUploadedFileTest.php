<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\InterviewService;

use App\Constant\SerializerConstant;
use App\Entity\Interview;
use App\Exception\Interview\InterviewImportException;
use App\Model\Export\InterviewExport;
use Prophecy\Argument;
use stdClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group unit
 */
class HandleUploadedFileTest extends AbstractTestInterviewService
{
    /** @dataProvider providerTestHandleUploadedFile */
    public function testHandleUploadedFile($extension, $mimeType, $errorMessage, $interview): void
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
            $this->expectException(InterviewImportException::class);
            $this->expectExceptionMessage('error_message_translated');
        } else {
            $this->getProphecy(SerializerInterface::class)
                ->deserialize(
                    Argument::type('string'),
                    Argument::exact(InterviewExport::class),
                    Argument::exact(SerializerConstant::FORMAT_EXPORT)
                )
                ->shouldBeCalled()
                ->willReturn($interview);
        }

        $this->interviewService->handleUploadedFile($file);
    }

    public function providerTestHandleUploadedFile(): iterable
    {
        yield 'wrong_extension' => [
            'xml',
            'application/xml',
            'exception.interview.import.upload_file.extension',
            null,
        ];

        yield 'wrong_mime_type' => [
            'json',
            'application/xml',
            'exception.interview.import.upload_file.type',
            null,
        ];

        yield 'wrong_instanceof' => [
            'json',
            'application/json',
            'exception.interview.import.upload_file.invalid',
            new stdClass(),
        ];

        yield 'interview_data_lost' => [
            'json',
            'application/json',
            'exception.interview.import.upload_file.invalid',
            new InterviewExport(),
        ];

        yield 'handle_success' => [
            'json',
            'application/json',
            null,
            (new InterviewExport())->setInterview(new Interview()),
        ];
    }
}
