<?php

declare(strict_types=1);

namespace App\Tests;

use InvalidArgumentException;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class CustomTestCase extends WebTestCase
{
    use ProphecyTrait;

    protected const CACHE_DIR = __DIR__ . '/../var/cache/test';

    /** @var array|ObjectProphecy[] */
    protected $prophecies = [];

    /** @var array */
    protected $reveals = [];

    public function tearDown(): void
    {
        unset($this->reveals);
        unset($this->prophecies);

        parent::tearDown();
    }

    /**
     * @param array|string[] $collection
     */
    protected function setProphecies(array $collection): void
    {
        foreach ($collection as $item) {
            $this->setProphecy($item);
        }
    }

    /**
     * @param string $classname
     */
    protected function setProphecy(string $classname): void
    {
        $this->prophecies[$classname] = $this->prophesize($classname);
    }

    /**
     * @param string $classname
     *
     * @return ObjectProphecy
     */
    protected function getProphecy(string $classname): ObjectProphecy
    {
        $this->prophecyResolver($classname);

        return $this->prophecies[$classname];
    }

    /**
     * @param string $classname
     *
     * @return mixed|object
     */
    protected function getReveal(string $classname)
    {
        $this->prophecyResolver($classname);

        return $this->reveals[$classname] ?? $this->reveals[$classname] = $this->getProphecy($classname)->reveal();
    }

    /**
     * @param string $classname
     *
     * @throws InvalidArgumentException
     */
    private function prophecyResolver(string $classname): void
    {
        if (empty($this->prophecies[$classname])) {
            throw new InvalidArgumentException(sprintf('Prophecy not found for %s', $classname));
        }
    }
}
