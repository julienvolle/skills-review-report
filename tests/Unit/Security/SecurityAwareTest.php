<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Security\SecurityAwareTrait;
use App\Tests\CustomTestCase;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

/**
 * @group unit
 */
class SecurityAwareTest extends CustomTestCase
{
    public function setUp(): void
    {
        $this->setProphecy(Security::class);
    }

    public function testTrait(): void
    {
        $object = new class () {
            use SecurityAwareTrait;
        };

        self::assertTrue(method_exists($object, 'setSecurity'));
        self::assertTrue(method_exists($object, 'denyAccessUnlessGranted'));
    }

    public function testDenyAccessUnlessGranted(): void
    {
        $this->getProphecy(Security::class)
            ->isGranted(
                Argument::exact('attributes'),
                Argument::exact('subject')
            )
            ->shouldBeCalledTimes(2)
            ->willReturn(true, false);

        $object = new class () {
            use SecurityAwareTrait;
        };
        $object->setSecurity($this->getReveal(Security::class));
        $object->denyAccessUnlessGranted('attributes', 'subject');

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('message');

        $object->denyAccessUnlessGranted('attributes', 'subject', 'message');
    }
}
