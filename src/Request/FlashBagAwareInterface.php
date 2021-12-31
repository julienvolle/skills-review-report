<?php

declare(strict_types=1);

namespace App\Request;

use LogicException;

interface FlashBagAwareInterface
{
    /**
     * Adds a flash message to the current session for type.
     *
     * Cf. Symfony\Bundle\FrameworkBundle\Controller\AbstractController
     *
     * @throws LogicException
     */
    public function addFlash(string $type, $message): void;
}
