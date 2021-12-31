<?php

declare(strict_types=1);

namespace App\Model;

class Semver
{
    private int $major;
    private int $minor;
    private int $patch;
    private ?string $label;

    public function __construct(int $major, int $minor, int $patch, ?string $label = null)
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->label = $label;
    }

    public function __toString(): string
    {
        $version = \implode('.', [
            $this->major,
            $this->minor,
            $this->patch,
        ]);

        if ($this->label) {
            $version .= '-' . $this->label;
        }

        return $version;
    }

    public function getMajor(): int
    {
        return $this->major;
    }

    public function setMajor(int $major): self
    {
        $this->major = $major;

        return $this;
    }

    public function getMinor(): int
    {
        return $this->minor;
    }

    public function setMinor(int $minor): self
    {
        $this->minor = $minor;

        return $this;
    }

    public function getPatch(): int
    {
        return $this->patch;
    }

    public function setPatch(int $patch): self
    {
        $this->patch = $patch;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
