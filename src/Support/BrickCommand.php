<?php

declare(strict_types=1);

namespace Awcodes\Mason\Support;

use Illuminate\Contracts\Support\Arrayable;

readonly class BrickCommand implements Arrayable
{
    public function __construct(
        public string $name,
        public array $arguments = [],
    ) {}

    public static function make(string $name, array $arguments = []): static
    {
        return app(static::class, ['name' => $name, 'arguments' => $arguments]);
    }

    /**
     * @param  string|null  $parent  path of the container brick to insert into, e.g. "2" or "2.0"; null for the top level
     */
    public static function insertBrick(array $brick, int $position, ?string $parent = null): static
    {
        return static::make('insertBrick', array_filter([
            'brick' => $brick,
            'position' => $position,
            'parent' => $parent,
        ], fn (mixed $value): bool => $value !== null));
    }

    /**
     * @param  int|string  $index  index at the top level, or the path of a nested brick, e.g. "2.0"
     */
    public static function updateBrick(int | string $index, array $brick): static
    {
        return static::make('updateBrick', [
            'index' => $index,
            'brick' => $brick,
        ]);
    }

    /**
     * @param  int|string  $index  index at the top level, or the path of a nested brick, e.g. "2.0"
     */
    public static function deleteBrick(int | string $index): static
    {
        return static::make('deleteBrick', [
            'index' => $index,
        ]);
    }

    /**
     * @param  int|string  $from  index at the top level, or the path of a nested brick, e.g. "2.0"
     * @param  int  $to  the brick's index in its new parent after the move
     * @param  string|null  $parent  path of the container to move into, "" for the top level; null keeps the parent
     */
    public static function moveBrick(int | string $from, int $to, ?string $parent = null): static
    {
        return static::make('moveBrick', array_filter([
            'from' => $from,
            'to' => $to,
            'parent' => $parent,
        ], fn (mixed $value): bool => $value !== null));
    }

    /**
     * @return array{name: string, arguments: array}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'arguments' => $this->arguments,
        ];
    }
}
