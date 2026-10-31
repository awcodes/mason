<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Brick;

class AnchorBrick extends Brick
{
    public static function getId(): string
    {
        return 'anchor-brick';
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        $anchor = $config['anchor'] ?? '';
        $title = $config['title'] ?? '';

        return "<section id=\"{$anchor}\">{$title}</section>";
    }

    public static function duplicateConfig(array $config): array
    {
        return [...$config, 'anchor' => null];
    }
}
