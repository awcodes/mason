<?php

declare(strict_types=1);

namespace Awcodes\Mason\Contracts;

use Awcodes\Mason\Brick;
use Awcodes\Mason\Support\BrickChildren;

/**
 * A brick that other bricks can be placed in.
 *
 * Its bricks are rendered by Mason, not by the brick itself, so that the editor
 * can wrap each of them in its controls. The brick only decides where they go.
 */
interface ContainsBricks
{
    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>|null  $data
     */
    public static function toHtmlWithChildren(array $config, BrickChildren $children, ?array $data = null): ?string;

    /**
     * The bricks that may be placed in this one, as classes or ids. Null allows every brick of the field.
     *
     * @return array<class-string<Brick>|string>|null
     */
    public static function getAllowedChildren(): ?array;
}
