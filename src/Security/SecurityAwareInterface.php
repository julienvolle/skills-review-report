<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

interface SecurityAwareInterface
{
    /**
     * Throws an exception unless the attribute is granted against the current authentication token and optionally
     * supplied subject.
     *
     * Cf. Symfony\Bundle\FrameworkBundle\Controller\AbstractController
     *
     * @throws AccessDeniedException
     */
    public function denyAccessUnlessGranted($attributes, $subject = null, string $message = 'Access Denied.'): void;
}
