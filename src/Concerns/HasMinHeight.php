<?php

declare(strict_types=1);

namespace Awcodes\Mason\Concerns;

use Closure;

trait HasMinHeight
{
    protected string | Closure | null $minHeight = null;

    public function minHeight(string | Closure | null $height): static
    {
        $this->minHeight = $height;

        return $this;
    }

    public function getMinHeight(): ?string
    {
        return $this->evaluate($this->minHeight);
    }
}
