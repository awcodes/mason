<?php

declare(strict_types=1);

namespace Awcodes\Mason\Concerns;

use Awcodes\Mason\Brick;
use Awcodes\Mason\Bricks\Section;
use Closure;

trait HasBricks
{
    protected array | Closure | null $bricks = null;

    /**
     * @var array<string, class-string<Brick>>
     */
    protected array $cachedBricks;

    /**
     * @param  array<class-string<Brick>> | Closure | null  $bricks
     */
    public function bricks(array | Closure | null $bricks): static
    {
        $this->bricks = $bricks;

        return $this;
    }

    /**
     * @return array<class-string<Brick>>
     */
    public function getBricks(): array
    {
        return $this->evaluate($this->bricks) ?? [
            Section::class,
        ];
    }

    /**
     * @return array<string, class-string<Brick>>
     */
    public function getCachedBricks(): array
    {
        if (isset($this->cachedBricks)) {
            return $this->cachedBricks;
        }

        foreach ($this->getBricks() as $brick) {
            $this->cachedBricks[$brick::getId()] = $brick;
        }

        return $this->cachedBricks;
    }

    /**
     * @return ?class-string<Brick>
     */
    public function getBrick(string $id): ?string
    {
        return $this->getCachedBricks()[$id] ?? null;
    }
}
