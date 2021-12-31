<?php

declare(strict_types=1);

namespace App\Request;

use LogicException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

trait FlashBagAwareTrait
{
    protected ?RequestStack $requestStack = null;

    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    public function addFlash(string $type, $message): void
    {
        if (!$this->requestStack) {
            throw new LogicException('The requestStack is not available.');
        }

        try {
            $this->requestStack->getSession()->getFlashBag()->add($type, $message);
        } catch (SessionNotFoundException $e) {
            $message = 'You cannot use the addFlash method if sessions are disabled. ' .
                'Enable them in "config/packages/framework.yaml".';
            throw new LogicException($message, $e->getCode(), $e);
        }
    }
}
