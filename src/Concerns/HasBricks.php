<?php

declare(strict_types=1);

namespace Awcodes\Mason\Concerns;

use Awcodes\Mason\Brick;
use Awcodes\Mason\BrickGroup;
use Awcodes\Mason\Bricks\Section;
use Closure;

trait HasBricks
{
    protected array | Closure | null $bricks = null;

    protected ?string $bricksSortDirection = null;

    /**
     * @var array<string, class-string<Brick>>
     */
    protected array $cachedBricks;

    /**
     * @param  array<class-string<Brick>|BrickGroup> | Closure | null  $bricks
     */
    public function bricks(array | Closure | null $bricks): static
    {
        $this->bricks = $bricks;

        return $this;
    }

    public function sortBricks(?string $direction = 'asc'): static
    {
        $this->bricksSortDirection = $direction;

        return $this;
    }

    public function getBricksSortDirection(): ?string
    {
        return $this->bricksSortDirection;
    }

    /**
     * @return array<class-string<Brick>|BrickGroup>
     */
    public function getBricks(): array
    {
        $bricks = $this->evaluate($this->bricks) ?? [
            Section::class,
        ];

        if ($this->bricksSortDirection !== null) {
            usort(
                $bricks,
                function ($a, $b): int {
                    $labelA = $a instanceof BrickGroup ? $a->getLabel() : $a::getLabel();
                    $labelB = $b instanceof BrickGroup ? $b->getLabel() : $b::getLabel();

                    return $this->bricksSortDirection === 'asc'
                        ? $labelA <=> $labelB
                        : $labelB <=> $labelA;
                }
            );
        }

        return $bricks;
    }

    /**
     * Returns a flat array of brick class names, unwrapping any BrickGroups.
     *
     * @return array<class-string<Brick>>
     */
    public function getFlatBricks(): array
    {
        $flat = [];

        foreach ($this->getBricks() as $item) {
            if ($item instanceof BrickGroup) {
                foreach ($item->getBricks() as $brick) {
                    $flat[] = $brick;
                }
            } else {
                $flat[] = $item;
            }
        }

        return $flat;
    }

    /**
     * @return array<string, class-string<Brick>>
     */
    public function getCachedBricks(): array
    {
        if (isset($this->cachedBricks)) {
            return $this->cachedBricks;
        }

        foreach ($this->getBricks() as $item) {
            if ($item instanceof BrickGroup) {
                foreach ($item->getBricks() as $brick) {
                    $this->cachedBricks[$brick::getId()] = $brick;
                }
            } else {
                $this->cachedBricks[$item::getId()] = $item;
            }
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
