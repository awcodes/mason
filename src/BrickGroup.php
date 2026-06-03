<?php

declare(strict_types=1);

namespace Awcodes\Mason;

class BrickGroup
{
    protected array $bricks = [];

    final public function __construct(protected string $label) {}

    public static function make(string $label): static
    {
        return new static($label);
    }

    /**
     * @param  array<class-string<Brick>>  $bricks
     */
    public function bricks(array $bricks): static
    {
        $this->bricks = $bricks;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return array<class-string<Brick>>
     */
    public function getBricks(): array
    {
        return $this->bricks;
    }
}
