<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Brick;
use Awcodes\Mason\Contracts\ContainsBricks;
use Awcodes\Mason\Support\BrickChildren;

class RestrictedContainerBrick extends Brick implements ContainsBricks
{
    public static function getId(): string
    {
        return 'restricted-container-brick';
    }

    public static function toHtmlWithChildren(array $config, BrickChildren $children, ?array $data = null): ?string
    {
        return '<div class="restricted">' . $children->toHtml() . '</div>';
    }

    public static function getAllowedChildren(): ?array
    {
        return [SimpleBrick::class];
    }
}
