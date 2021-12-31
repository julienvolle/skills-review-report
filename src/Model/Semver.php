<?php

declare(strict_types=1);

namespace App\Model;

class Semver
{
    /** @var int */
    private $major;

    /** @var int */
    private $minor;

    /** @var int */
    private $patch;

    /** @var string|null */
    private $label;

    /**
     * @param int         $major
     * @param int         $minor
     * @param int         $patch
     * @param string|null $label
     */
    public function __construct(
        int $major,
        int $minor,
        int $patch,
        ?string $label = null
    ) {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $version = implode('.', [
            $this->major,
            $this->minor,
            $this->patch,
        ]);

        if ($this->label) {
            $version .= '-' . $this->label;
        }

        return $version;
    }

    /**
     * @return int
     */
    public function getMajor(): int
    {
        return $this->major;
    }

    /**
     * @param int $major
     *
     * @return self
     */
    public function setMajor(int $major): self
    {
        $this->major = $major;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinor(): int
    {
        return $this->minor;
    }

    /**
     * @param int $minor
     *
     * @return self
     */
    public function setMinor(int $minor): self
    {
        $this->minor = $minor;

        return $this;
    }

    /**
     * @return int
     */
    public function getPatch(): int
    {
        return $this->patch;
    }

    /**
     * @param int $patch
     *
     * @return self
     */
    public function setPatch(int $patch): self
    {
        $this->patch = $patch;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     *
     * @return self
     */
    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
