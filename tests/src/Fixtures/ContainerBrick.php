<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Brick;
use Awcodes\Mason\Contracts\ContainsBricks;
use Awcodes\Mason\Support\BrickChild;
use Awcodes\Mason\Support\BrickChildren;

class ContainerBrick extends Brick implements ContainsBricks
{
    public static function getId(): string
    {
        return 'container-brick';
    }

    public static function toHtmlWithChildren(array $config, BrickChildren $children, ?array $data = null): ?string
    {
        $items = array_map(
            fn (BrickChild $child): string => '<div class="cell" data-span="' . $child->getAttribute('layout.span', 1) . '">' . $child->toHtml() . '</div>',
            iterator_to_array($children),
        );

        return '<div class="container" data-title="' . ($config['title'] ?? '') . '">' . implode('', $items) . $children->dropZone()->toHtml() . '</div>';
    }

    public static function getAllowedChildren(): ?array
    {
        return null;
    }
}
