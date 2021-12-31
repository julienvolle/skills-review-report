<?php

declare(strict_types=1);

namespace App\Service;

use App\Constant\CacheConstant;
use App\Exception\Semver\SemverCacheException;
use App\Exception\Semver\SemverFileContentsException;
use App\Exception\Semver\SemverFileLoaderException;
use App\Loader\SemverFileLoader;
use App\Model\Semver;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SemverService
{
    /** @var SemverFileLoader */
    private $loader;

    /** @var CacheItemPoolInterface */
    private $cache;

    /** @var SerializerInterface */
    private $serializer;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param SemverFileLoader       $loader
     * @param CacheItemPoolInterface $cache
     * @param SerializerInterface    $serializer
     * @param TranslatorInterface    $translator
     */
    public function __construct(
        SemverFileLoader $loader,
        CacheItemPoolInterface $cache,
        SerializerInterface $serializer,
        TranslatorInterface $translator
    ) {
        $this->loader = $loader;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->translator = $translator;
    }

    /**
     * Get current version from cache or semver file
     *
     * @throws SemverCacheException|SemverFileLoaderException|SemverFileContentsException
     *
     * @return string
     */
    public function getVersion(): string
    {
        try {
            $version = $this->cache->getItem(CacheConstant::APP_VERSION_KEY);
            if ($version->isHit()) {
                return $version->get();
            }
            $version->set($this->loadVersion());
            $version->expiresAfter(CacheConstant::APP_VERSION_TTL);
            $this->cache->save($version);
        } catch (InvalidArgumentException $e) {
            throw new SemverCacheException($e->getMessage(), [], $e);
        }

        return $version->get();
    }

    /**
     * Load semver file contents
     *
     * @throws SemverFileLoaderException|SemverFileContentsException
     *
     * @return string
     */
    private function loadVersion(): string
    {
        try {
            $json = $this->loader->load(__DIR__ . '/../../.semver');
        } catch (Exception $e) {
            throw new SemverFileLoaderException($e->getMessage(), [], $e);
        }

        /** @var Semver $semver */
        $semver = $this->serializer->deserialize($json, Semver::class, 'json');
        if (!$semver instanceof Semver) {
            throw new SemverFileContentsException(
                $this->translator->trans('exception.semver.invalid', [], 'errors')
            );
        }

        return (string) $semver;
    }
}
