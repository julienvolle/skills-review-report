<?php

declare(strict_types=1);

namespace App\Tests\Unit\ArgumentValueResolver;

use App\ArgumentValueResolver\InterviewArgumentValueResolver;
use App\Entity\Interview;
use App\Repository\InterviewRepository;
use App\Tests\CustomTestCase;
use Prophecy\Argument;
use stdClass;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group unit
 */
class InterviewArgumentValueResolverTest extends CustomTestCase
{
    public function setUp(): void
    {
        $this->setProphecies([
            Interview::class,
            ParameterBag::class,
            Request::class,
            InterviewRepository::class,
            TranslatorInterface::class,
            ArgumentMetadata::class,
        ]);

        $this->getProphecy(Request::class)->attributes = $this->getReveal(ParameterBag::class);
    }

    public function testResolve(): void
    {
        $uuid = (string) Uuid::v4();

        $this->getProphecy(ParameterBag::class)
            ->set(
                Argument::exact('interview'),
                Argument::exact($this->getReveal(Interview::class))
            )
            ->shouldBeCalledOnce();

        $this->getProphecy(Request::class)
            ->get(Argument::exact('interview_id'), Argument::exact(''))
            ->shouldBeCalledOnce()
            ->willReturn($uuid);

        $this->getProphecy(InterviewRepository::class)
            ->findOneByGuid(Argument::exact($uuid))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Interview::class));

        $this->getProphecy(ArgumentMetadata::class)
            ->getName()
            ->shouldBeCalledOnce()
            ->willReturn('interview');

        $resolver = new InterviewArgumentValueResolver(
            $this->getReveal(InterviewRepository::class),
            $this->getReveal(TranslatorInterface::class)
        );

        $result = iterator_to_array($resolver->resolve(
            $this->getReveal(Request::class),
            $this->getReveal(ArgumentMetadata::class)
        ));
        self::assertIsIterable($result);
        self::assertEquals([$this->getReveal(Interview::class)], $result);
    }

    /** @dataProvider providerTestResolveFailure */
    public function testResolveFailure(string $uuid): void
    {
        $this->getProphecy(Request::class)
            ->get(Argument::exact('interview_id'), Argument::exact(''))
            ->shouldBeCalledOnce()
            ->willReturn($uuid);

        if (!Uuid::isValid($uuid)) {
            $this->getProphecy(TranslatorInterface::class)
                ->trans(
                    Argument::exact('exception.interview.400.wrong_guid'),
                    Argument::type('array'),
                    Argument::exact('errors')
                )
                ->shouldBeCalledOnce()
                ->willReturn('bad_request');

            $this->expectException(BadRequestException::class);
            $this->expectExceptionMessage('bad_request');
        } else {
            $this->getProphecy(InterviewRepository::class)
                ->findOneByGuid(Argument::exact($uuid))
                ->shouldBeCalledOnce()
                ->willReturn(null);

            $this->getProphecy(TranslatorInterface::class)
                ->trans(
                    Argument::exact('exception.interview.404'),
                    Argument::type('array'),
                    Argument::exact('errors')
                )
                ->shouldBeCalledOnce()
                ->willReturn('not_found');

            $this->expectException(NotFoundHttpException::class);
            $this->expectExceptionMessage('not_found');
        }

        $resolver = new InterviewArgumentValueResolver(
            $this->getReveal(InterviewRepository::class),
            $this->getReveal(TranslatorInterface::class)
        );

        iterator_to_array($resolver->resolve(
            $this->getReveal(Request::class),
            $this->getReveal(ArgumentMetadata::class)
        ));
    }

    public function providerTestResolveFailure(): iterable
    {
        yield 'no_uuid'             => [''];
        yield 'invalid_uuid'        => ['invalid_uuid'];
        yield 'interview_not_found' => [(string) Uuid::v4()];
    }

    /** @dataProvider providerTestSupports */
    public function testSupports(string $assertion, string $type, ?string $id): void
    {
        $this->getProphecy(ArgumentMetadata::class)
            ->getType()
            ->shouldBeCalledOnce()
            ->willReturn($type);

        if ($type === Interview::class) {
            $this->getProphecy(Request::class)
                ->get(Argument::exact('interview_id'))
                ->shouldBeCalledOnce()
                ->willReturn($id);
        }

        $resolver = new InterviewArgumentValueResolver(
            $this->getReveal(InterviewRepository::class),
            $this->getReveal(TranslatorInterface::class)
        );

        self::$assertion($resolver->supports(
            $this->getReveal(Request::class),
            $this->getReveal(ArgumentMetadata::class)
        ));
    }

    public function providerTestSupports(): iterable
    {
        yield 'ko_type' => ['assertFalse',  stdClass::class, (string) Uuid::v4()];
        yield 'ko_uuid' => ['assertFalse', Interview::class,                null];
        yield 'ok'      => ['assertTrue',  Interview::class, (string) Uuid::v4()];
    }
}
