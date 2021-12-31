<?php

declare(strict_types=1);

namespace App\Service;

use App\Annotation\Encryption;
use App\Provider\EncryptionProvider;
use DateTime;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EncryptionService
{
    private Reader $annotationReader;
    private EncryptionProvider $encryptionProvider;

    public function __construct(Reader $annotationReader, EncryptionProvider $encryptionProvider)
    {
        $this->annotationReader = $annotationReader;
        $this->encryptionProvider = $encryptionProvider;
    }

    public function encrypt(object $data): void
    {
        if (\method_exists($data, 'isSecured') && !$data->isSecured()) {
            $this->process($data);
            $data->setSecured(true);
            $data->setSecuredAt(new DateTime());
        }
    }

    public function decrypt(object $data): void
    {
        if (\method_exists($data, 'isSecured') && $data->isSecured()) {
            $this->process($data, true);
            $data->setSecured(false);
            $data->setSecuredAt(null);
        }
    }

    private function process(object $data, bool $revert = false): void
    {
        $class = ClassUtils::getClass($data);
        $method = $revert ? 'decrypt' : 'encrypt';
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($this->getProperties($class) as $property) {
            $annotation = $this->annotationReader->getPropertyAnnotation($property, Encryption::class);
            if ($annotation && \is_string($value = $propertyAccessor->getValue($data, $property->getName()))) {
                $value = $this->encryptionProvider->getEncryption($annotation->name)->$method($value);
                if ($annotation->maxLength === null || \strlen($value) <= $annotation->maxLength) {
                    $propertyAccessor->setValue($data, $property->getName(), $value);
                }
            }
        }
    }

    private function getProperties(string $class): array
    {
        $refClass = ClassUtils::newReflectionClass($class);
        $properties = $refClass->getProperties();
        if ($parentClass = $refClass->getParentClass()) {
            $properties = \array_merge($properties, $this->getProperties($parentClass->getName()));
        }

        return \array_values(\array_filter($properties));
    }
}
