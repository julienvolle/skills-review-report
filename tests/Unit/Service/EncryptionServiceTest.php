<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Annotation\Encryption;
use App\Constant\SecurityConstant;
use App\Entity\Common\SecuredEntityTrait;
use App\Provider\EncryptionProvider;
use App\Security\Encryption\EncryptionInterface;
use App\Service\EncryptionService;
use App\Tests\CustomTestCase;
use DateTime;
use Doctrine\Common\Annotations\Reader;
use Prophecy\Argument;
use ReflectionProperty;
use stdClass;

/**
 * @group unit
 */
class EncryptionServiceTest extends CustomTestCase
{
    private ?EncryptionService $service = null;

    public function setUp(): void
    {
        $this->setProphecies([
            Reader::class,
            EncryptionProvider::class,
            EncryptionInterface::class,
        ]);

        $this->service = new EncryptionService(
            $this->getReveal(Reader::class),
            $this->getReveal(EncryptionProvider::class)
        );
    }

    public function tearDown(): void
    {
        unset($this->service);

        parent::tearDown();
    }

    public function testEncryptAnEntityWithoutSecured(): void
    {
        $this->getProphecy(Reader::class)
            ->getPropertyAnnotation(
                Argument::type(ReflectionProperty::class),
                Argument::exact(Encryption::class)
            )
            ->shouldNotBeCalled();

        $this->service->encrypt(new stdClass());
    }

    public function testDecryptAnEntityWithoutSecured(): void
    {
        $this->getProphecy(Reader::class)
            ->getPropertyAnnotation(
                Argument::type(ReflectionProperty::class),
                Argument::exact(Encryption::class)
            )
            ->shouldNotBeCalled();

        $this->service->decrypt(new stdClass());
    }

    public function testEncryptAnEntityAlreadyEncrypted(): void
    {
        $entity = new class () {
            use SecuredEntityTrait;
        };
        $entity->setSecured(true);
        self::assertTrue($entity->isSecured());

        $this->getProphecy(Reader::class)
            ->getPropertyAnnotation(
                Argument::type(ReflectionProperty::class),
                Argument::exact(Encryption::class)
            )
            ->shouldNotBeCalled();

        $this->service->encrypt($entity);
    }

    public function testDecryptAnEntityAlreadyDecrypted(): void
    {
        $entity = new class () {
            use SecuredEntityTrait;
        };
        $entity->setSecured(false);
        self::assertFalse($entity->isSecured());

        $this->getProphecy(Reader::class)
            ->getPropertyAnnotation(
                Argument::type(ReflectionProperty::class),
                Argument::exact(Encryption::class)
            )
            ->shouldNotBeCalled();

        $this->service->decrypt($entity);
    }

    /** @dataProvider providerTestProcess */
    public function testProcess(string $data, string $encryptedData, ?int $maxLength, bool $processed): void
    {
        $entity = new class () extends stdClass {
            use SecuredEntityTrait;

            /** @Encryption(name=SecurityConstant::ENCRYPTION_BASE64) */
            private ?string $field = null;
            public function setField(?string $field): void
            {
                $this->field = $field;
            }
            public function getField(): ?string
            {
                return $this->field;
            }
        };
        $entity->setField($data);
        $entity->setSecured(false);
        self::assertSame($data, $entity->getField());
        self::assertFalse($entity->isSecured());
        self::assertNull($entity->getSecuredAt());

        $annotation = new Encryption(['name' => SecurityConstant::ENCRYPTION_BASE64, 'maxLength' => $maxLength]);
        self::assertSame(SecurityConstant::ENCRYPTION_BASE64, $annotation->name);
        self::assertSame($maxLength, $annotation->maxLength);

        $this->getProphecy(Reader::class)
            ->getPropertyAnnotation(
                Argument::that(function (ReflectionProperty $arg) {
                    return $arg->getName() === 'field';
                }),
                Argument::exact(Encryption::class)
            )
            ->shouldBeCalled()
            ->willReturn($annotation); // fields with @Encryption annotation

        $this->getProphecy(Reader::class)
            ->getPropertyAnnotation(
                Argument::that(function (ReflectionProperty $arg) {
                    return $arg->getName() !== 'field';
                }),
                Argument::exact(Encryption::class)
            )
            ->shouldBeCalled()
            ->willReturn(null); // fields without @Encryption annotation

        $this->getProphecy(EncryptionProvider::class)
            ->getEncryption(Argument::exact($annotation->name))
            ->shouldBeCalled()
            ->willReturn($this->getReveal(EncryptionInterface::class));

        $this->getProphecy(EncryptionInterface::class)
            ->encrypt(Argument::exact($data))
            ->shouldBeCalled()
            ->willReturn($encryptedData);

        $this->service->encrypt($entity);
        self::assertSame($processed ? $encryptedData : $data, $entity->getField());
        self::assertTrue($entity->isSecured());
        self::assertInstanceOf(DateTime::class, $entity->getSecuredAt());

        $this->getProphecy(EncryptionInterface::class)
            ->decrypt(Argument::exact($processed ? $encryptedData : $data))
            ->shouldBeCalled()
            ->willReturn($data);

        $this->service->decrypt($entity);
        self::assertSame($data, $entity->getField());
        self::assertFalse($entity->isSecured());
        self::assertNull($entity->getSecuredAt());
    }

    public function providerTestProcess(): iterable
    {
        yield 'no_limit'     => ['test', 'test_encrypted',                                null,  true];
        yield 'max_limit'    => ['test', 'test_encrypted',     \strlen('test_encrypted'),  true];
        yield 'under_limit'  => ['test', 'test_encrypted', \strlen('test_encrypted') + 1,  true];
        yield 'exceed_limit' => ['test', 'test_encrypted', \strlen('test_encrypted') - 1, false];
    }
}
