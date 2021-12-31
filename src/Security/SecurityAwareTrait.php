<?php

declare(strict_types=1);

namespace App\Security;

use LogicException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

trait SecurityAwareTrait
{
    protected ?Security $security = null;

    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    public function denyAccessUnlessGranted($attributes, $subject = null, string $message = 'Access Denied.'): void
    {
        if (!$this->security->isGranted($attributes, $subject)) {
            // @codeCoverageIgnoreStart
            if (!class_exists(AccessDeniedException::class)) {
                throw new LogicException(
                    'You cannot use the "denyAccessUnlessGranted" method if the Security component is not available. ' .
                    'Try running "composer require symfony/security-bundle".'
                );
            }
            // @codeCoverageIgnoreEnd

            $exception = new AccessDeniedException($message);
            $exception->setAttributes($attributes);
            $exception->setSubject($subject);

            throw $exception;
        }
    }
}
