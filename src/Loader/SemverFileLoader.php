<?php

declare(strict_types=1);

namespace App\Loader;

use LogicException;
use Symfony\Component\Config\Loader\FileLoader;

class SemverFileLoader extends FileLoader
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) on parameter $type
     */
    public function load($resource, string $type = null): string
    {
        $contents = @\file_get_contents($this->locator->locate($resource));
        if (!$contents) {
            throw new LogicException(\sprintf('File "%s" is not found', $resource));
        }

        return $contents;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) on parameter $type
     */
    public function supports($resource, string $type = null): bool
    {
        return \is_string($resource) && 'semver' === \pathinfo($resource, PATHINFO_EXTENSION);
    }
}
