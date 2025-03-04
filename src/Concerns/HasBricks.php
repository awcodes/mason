<?php

namespace Awcodes\Mason\Concerns;

use Awcodes\Mason\Bricks\Section;
use Closure;

trait HasBricks
{
    protected array | Closure | null $bricks = null;

    public function bricks(array | Closure $bricks): static
    {
        $this->bricks = $bricks;

        return $this;
    }

    public function getBricks(): array
    {
        return $this->evaluate($this->bricks) ?? [
            Section::make(),
        ];
    }
}
