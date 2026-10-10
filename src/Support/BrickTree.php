<?php

declare(strict_types=1);

namespace Awcodes\Mason\Support;

/**
 * Reads and changes nested Mason content.
 *
 * A brick that contains other bricks keeps them under its "content" key, so a
 * brick is addressed by a path of indexes: "2" is the third brick at the top
 * level, "2.0" the first brick inside it. The root is addressed by null or "".
 */
class BrickTree
{
    /**
     * @return list<int>
     */
    public static function parse(string | int | array | null $path): array
    {
        if (in_array($path, [null, '', []], true)) {
            return [];
        }

        if (is_int($path)) {
            return [$path];
        }

        if (is_string($path)) {
            $path = explode('.', $path);
        }

        return array_values(array_map(intval(...), $path));
    }

    public static function format(array $path): string
    {
        return implode('.', $path);
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<string, mixed>|null
     */
    public static function get(array $blocks, string | int | array | null $path): ?array
    {
        $path = static::parse($path);

        if ($path === []) {
            return null;
        }

        $block = null;

        foreach ($path as $index) {
            if (! isset($blocks[$index]) || ! is_array($blocks[$index])) {
                return null;
            }

            $block = $blocks[$index];
            $blocks = is_array($block['content'] ?? null) ? $block['content'] : [];
        }

        return $block;
    }

    /**
     * The bricks inside the brick at the path, or the top level for the root.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function children(array $blocks, string | int | array | null $parent): array
    {
        if (static::parse($parent) === []) {
            return $blocks;
        }

        $block = static::get($blocks, $parent);

        return is_array($block['content'] ?? null) ? $block['content'] : [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>  $brick
     * @return array<int, array<string, mixed>>
     */
    public static function insert(array $blocks, string | int | array | null $parent, ?int $position, array $brick): array
    {
        return static::changeChildren($blocks, static::parse($parent), function (array $children) use ($position, $brick): array {
            $position = $position === null ? count($children) : max(0, min($position, count($children)));
            array_splice($children, $position, 0, [$brick]);

            return $children;
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>  $brick
     * @return array<int, array<string, mixed>>
     */
    public static function update(array $blocks, string | int | array $path, array $brick): array
    {
        $path = static::parse($path);
        $index = array_pop($path);

        if ($index === null) {
            return $blocks;
        }

        return static::changeChildren($blocks, $path, function (array $children) use ($index, $brick): array {
            if (isset($children[$index])) {
                $children[$index] = $brick;
            }

            return $children;
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function remove(array $blocks, string | int | array $path): array
    {
        $path = static::parse($path);
        $index = array_pop($path);

        if ($index === null) {
            return $blocks;
        }

        return static::changeChildren($blocks, $path, function (array $children) use ($index): array {
            if (isset($children[$index])) {
                array_splice($children, $index, 1);
            }

            return $children;
        });
    }

    /**
     * Moves a brick to the given position inside another (or the same) parent.
     * The position counts the target's bricks after the moved brick was taken out.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function move(array $blocks, string | int | array $from, string | int | array | null $toParent, int $position): array
    {
        $from = static::parse($from);
        $toParent = static::parse($toParent);
        $brick = static::get($blocks, $from);

        // A brick can't be moved into itself
        if ($brick === null || $from === [] || static::isWithin($toParent, $from)) {
            return $blocks;
        }

        $blocks = static::remove($blocks, $from);

        // Taking the brick out shifts later siblings, which may include an ancestor of the target
        $depth = count($from) - 1;

        if (count($toParent) > $depth
            && array_slice($toParent, 0, $depth) === array_slice($from, 0, $depth)
            && $toParent[$depth] > $from[$depth]) {
            $toParent[$depth]--;
        }

        return static::insert($blocks, $toParent, $position, $brick);
    }

    /**
     * Whether the path is the other path or lies inside it.
     */
    public static function isWithin(string | int | array | null $path, string | int | array | null $ancestor): bool
    {
        $path = static::parse($path);
        $ancestor = static::parse($ancestor);

        return $ancestor !== [] && array_slice($path, 0, count($ancestor)) === $ancestor;
    }

    /**
     * Calls the callback for every brick, parents before their children.
     *
     * @param  array<int, mixed>  $blocks
     * @param  callable(array<string, mixed>, string): void  $callback
     */
    public static function walk(array $blocks, callable $callback, array $parent = []): void
    {
        foreach ($blocks as $index => $block) {
            if (! is_array($block)) {
                continue;
            }

            $path = [...$parent, $index];
            $callback($block, static::format($path));

            if (is_array($block['content'] ?? null)) {
                static::walk($block['content'], $callback, $path);
            }
        }
    }

    /**
     * Replaces every brick with what the callback returns, parents before their children.
     *
     * @param  array<int, mixed>  $blocks
     * @param  callable(array<string, mixed>, string): array<string, mixed>  $callback
     * @return array<int, mixed>
     */
    public static function map(array $blocks, callable $callback, array $parent = []): array
    {
        foreach ($blocks as $index => $block) {
            if (! is_array($block)) {
                continue;
            }

            $path = [...$parent, $index];
            $block = $callback($block, static::format($path));

            if (is_array($block['content'] ?? null)) {
                $block['content'] = static::map($block['content'], $callback, $path);
            }

            $blocks[$index] = $block;
        }

        return $blocks;
    }

    /**
     * @param  array<int, mixed>  $blocks
     * @param  list<int>  $parent
     * @param  callable(array<int, mixed>): array<int, mixed>  $change
     * @return array<int, mixed>
     */
    protected static function changeChildren(array $blocks, array $parent, callable $change): array
    {
        if ($parent === []) {
            return array_values($change($blocks));
        }

        $index = array_shift($parent);

        if (! isset($blocks[$index]) || ! is_array($blocks[$index])) {
            return $blocks;
        }

        $children = is_array($blocks[$index]['content'] ?? null) ? $blocks[$index]['content'] : [];
        $blocks[$index]['content'] = static::changeChildren($children, $parent, $change);

        return $blocks;
    }
}
