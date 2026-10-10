<?php

declare(strict_types=1);

namespace Awcodes\Mason\Concerns;

use Awcodes\Mason\Brick;
use Awcodes\Mason\BrickGroup;
use Awcodes\Mason\Bricks\Section;
use Awcodes\Mason\Contracts\ContainsBricks;
use Closure;

trait HasBricks
{
    protected array | Closure | null $bricks = null;

    protected ?string $bricksSortDirection = null;

    /**
     * @var array<string, class-string<Brick>>|null
     */
    protected ?array $cachedBricks = null;

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
        if ($this->cachedBricks !== null) {
            return $this->cachedBricks;
        }

        $cached = [];

        foreach ($this->getFlatBricks() as $brick) {
            $cached[$brick::getId()] = $brick;
        }

        return $this->cachedBricks = $cached;
    }

    /**
     * @return ?class-string<Brick>
     */
    public function getBrick(string $id): ?string
    {
        return $this->getCachedBricks()[$id] ?? null;
    }

    /**
     * @return ?class-string<ContainsBricks>
     */
    public function getContainerBrick(?string $id): ?string
    {
        $brick = filled($id) ? $this->getBrick($id) : null;

        return $brick !== null && is_subclass_of($brick, ContainsBricks::class) ? $brick : null;
    }

    public function isContainerBrick(?string $id): bool
    {
        return $this->getContainerBrick($id) !== null;
    }

    /**
     * Whether a brick may be placed in another one. A null parent is the top level.
     */
    public function canContainBrick(?string $parentId, string $childId): bool
    {
        if ($this->getBrick($childId) === null) {
            return false;
        }

        if ($parentId === null) {
            return true;
        }

        $container = $this->getContainerBrick($parentId);

        if ($container === null) {
            return false;
        }

        $allowed = $container::getAllowedChildren();

        if ($allowed === null) {
            return true;
        }

        return in_array($childId, array_map(
            fn (string $brick): string => is_subclass_of($brick, Brick::class) ? $brick::getId() : $brick,
            $allowed,
        ), true);
    }

    /**
     * The ids of the bricks a container accepts. Used by the editor while dragging.
     *
     * @return list<string>
     */
    public function getAcceptedBrickIds(string $parentId): array
    {
        if (! $this->isContainerBrick($parentId)) {
            return [];
        }

        return array_values(array_filter(
            array_keys($this->getCachedBricks()),
            fn (string $id): bool => $this->canContainBrick($parentId, $id),
        ));
    }
}
