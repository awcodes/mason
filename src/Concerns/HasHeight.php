<?php

declare(strict_types=1);

namespace Awcodes\Mason\Concerns;

use Closure;

trait HasHeight
{
    protected string | Closure | null $minHeight = null;

    protected string | Closure | null $maxHeight = null;

    public function minHeight(string | Closure | null $height): static
    {
        $this->minHeight = $height;

        return $this;
    }

    public function maxHeight(string | Closure | null $height): static
    {
        $this->maxHeight = $height;

        return $this;
    }

    public function getMinHeight(): ?string
    {
        return $this->evaluate($this->minHeight);
    }

    public function getMaxHeight(): ?string
    {
        return $this->evaluate($this->maxHeight);
    }

    /**
     * The heights as CSS variables; without one, the stylesheet's default applies
     *
     * @return array<string>
     */
    public function getHeightStyles(): array
    {
        $minHeight = $this->getMinHeight();
        $maxHeight = $this->getMaxHeight();

        return array_values(array_filter([
            filled($minHeight) ? "--mason-min-height: {$minHeight}" : null,
            filled($maxHeight) ? "--mason-max-height: {$maxHeight}" : null,
        ]));
    }
}
