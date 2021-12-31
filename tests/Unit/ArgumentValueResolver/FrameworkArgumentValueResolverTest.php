<?php

declare(strict_types=1);

namespace App\Tests\Unit\ArgumentValueResolver;

use App\ArgumentValueResolver\FrameworkArgumentValueResolver;
use App\Entity\Framework;
use App\Repository\FrameworkRepository;
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
class FrameworkArgumentValueResolverTest extends CustomTestCase
{
    public function setUp(): void
    {
        $this->setProphecies([
            Framework::class,
            ParameterBag::class,
            Request::class,
            FrameworkRepository::class,
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
                Argument::exact('framework'),
                Argument::exact($this->getReveal(Framework::class))
            )
            ->shouldBeCalledOnce();

        $this->getProphecy(Request::class)
            ->get(Argument::exact('framework_id'), Argument::exact(''))
            ->shouldBeCalledOnce()
            ->willReturn($uuid);

        $this->getProphecy(FrameworkRepository::class)
            ->findOneByGuid(Argument::exact($uuid))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Framework::class));

        $this->getProphecy(ArgumentMetadata::class)
            ->getName()
            ->shouldBeCalledOnce()
            ->willReturn('framework');

        $resolver = new FrameworkArgumentValueResolver(
            $this->getReveal(FrameworkRepository::class),
            $this->getReveal(TranslatorInterface::class)
        );

        $result = iterator_to_array($resolver->resolve(
            $this->getReveal(Request::class),
            $this->getReveal(ArgumentMetadata::class)
        ));
        self::assertIsIterable($result);
        self::assertEquals([$this->getReveal(Framework::class)], $result);
    }

    /** @dataProvider providerTestResolveFailure */
    public function testResolveFailure(string $uuid): void
    {
        $this->getProphecy(Request::class)
            ->get(Argument::exact('framework_id'), Argument::exact(''))
            ->shouldBeCalledOnce()
            ->willReturn($uuid);

        if (!Uuid::isValid($uuid)) {
            $this->getProphecy(TranslatorInterface::class)
                ->trans(
                    Argument::exact('exception.framework.400.wrong_guid'),
                    Argument::type('array'),
                    Argument::exact('errors')
                )
                ->shouldBeCalledOnce()
                ->willReturn('bad_request');

            $this->expectException(BadRequestException::class);
            $this->expectExceptionMessage('bad_request');
        } else {
            $this->getProphecy(FrameworkRepository::class)
                ->findOneByGuid(Argument::exact($uuid))
                ->shouldBeCalledOnce()
                ->willReturn(null);

            $this->getProphecy(TranslatorInterface::class)
                ->trans(
                    Argument::exact('exception.framework.404'),
                    Argument::type('array'),
                    Argument::exact('errors')
                )
                ->shouldBeCalledOnce()
                ->willReturn('not_found');

            $this->expectException(NotFoundHttpException::class);
            $this->expectExceptionMessage('not_found');
        }

        $resolver = new FrameworkArgumentValueResolver(
            $this->getReveal(FrameworkRepository::class),
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
        yield 'framework_not_found' => [(string) Uuid::v4()];
    }

    /** @dataProvider providerTestSupports */
    public function testSupports(string $assertion, string $type, ?string $id): void
    {
        $this->getProphecy(ArgumentMetadata::class)
            ->getType()
            ->shouldBeCalledOnce()
            ->willReturn($type);

        if ($type === Framework::class) {
            $this->getProphecy(Request::class)
                ->get(Argument::exact('framework_id'))
                ->shouldBeCalledOnce()
                ->willReturn($id);
        }

        $resolver = new FrameworkArgumentValueResolver(
            $this->getReveal(FrameworkRepository::class),
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
        yield 'ko_uuid' => ['assertFalse', Framework::class,                null];
        yield 'ok'      => ['assertTrue',  Framework::class, (string) Uuid::v4()];
    }
}
