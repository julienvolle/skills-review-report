<?php

declare(strict_types=1);

namespace App\Tests\Unit\ParamConverter;

use App\Entity\Framework;
use App\ParamConverter\FrameworkParamConverter;
use App\Repository\FrameworkRepository;
use App\Tests\CustomTestCase;
use Prophecy\Argument;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @group unit
 */
class FrameworkParamConverterTest extends CustomTestCase
{
    public function setUp(): void
    {
        $this->setProphecies([
            Framework::class,
            ParameterBag::class,
            Request::class,
            FrameworkRepository::class,
            TranslatorInterface::class,
            ParamConverter::class,
        ]);

        $this->getProphecy(Request::class)->attributes = $this->getReveal(ParameterBag::class);
    }

    public function testApply(): void
    {
        $uuid = (string) Uuid::v4();

        $this->getProphecy(ParameterBag::class)
            ->set(
                Argument::exact('framework'),
                Argument::exact($this->getReveal(Framework::class))
            )
            ->shouldBeCalledOnce();

        $this->getProphecy(Request::class)
            ->get(Argument::exact('framework_id'))
            ->shouldBeCalledOnce()
            ->willReturn($uuid);

        $this->getProphecy(FrameworkRepository::class)
            ->findOneByGuid(Argument::exact($uuid))
            ->shouldBeCalledOnce()
            ->willReturn($this->getReveal(Framework::class));

        $this->getProphecy(ParamConverter::class)
            ->getClass()
            ->shouldBeCalledOnce()
            ->willReturn(Framework::class);
        $this->getProphecy(ParamConverter::class)
            ->getName()
            ->shouldBeCalledOnce()
            ->willReturn('framework');

        $converter = new FrameworkParamConverter(
            $this->getReveal(FrameworkRepository::class),
            $this->getReveal(TranslatorInterface::class)
        );

        self::assertTrue($converter->apply(
            $this->getReveal(Request::class),
            $this->getReveal(ParamConverter::class)
        ));
    }

    /** @dataProvider providerTestApplyFailure */
    public function testApplyFailure($uuid = null, $class = null): void
    {
        $this->getProphecy(Request::class)
            ->get(Argument::exact('framework_id'))
            ->shouldBeCalledOnce()
            ->willReturn($uuid);

        if ($uuid && Uuid::isValid($uuid)) {
            $this->getProphecy(ParamConverter::class)
                ->getClass()
                ->shouldBeCalledOnce()
                ->willReturn($class);
        }

        if ($class === Framework::class) {
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
        }

        $converter = new FrameworkParamConverter(
            $this->getReveal(FrameworkRepository::class),
            $this->getReveal(TranslatorInterface::class)
        );

        $result = $converter->apply(
            $this->getReveal(Request::class),
            $this->getReveal(ParamConverter::class)
        );

        if (!$this->getExpectedException()) {
            self::assertFalse($result);
        }
    }

    public function providerTestApplyFailure(): iterable
    {
        yield 'no_uuid'             => [null];
        yield 'invalid_uuid'        => ['invalid_uuid'];
        yield 'invalid_class'       => [(string) Uuid::v4(), 'invalid_class'];
        yield 'framework_not_found' => [(string) Uuid::v4(), Framework::class];
    }

    public function testSupports(): void
    {
        $this->getProphecy(ParamConverter::class)
            ->getName()
            ->shouldBeCalledTimes(2)
            ->willReturn('object', 'framework');

        $converter = new FrameworkParamConverter(
            $this->getReveal(FrameworkRepository::class),
            $this->getReveal(TranslatorInterface::class)
        );

        self::assertFalse($converter->supports($this->getReveal(ParamConverter::class)));
        self::assertTrue($converter->supports($this->getReveal(ParamConverter::class)));
    }
}
