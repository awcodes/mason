<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Brick;

class LimitedBrick extends Brick
{
    public static function getId(): string
    {
        return 'limited-brick';
    }

    public static function getMaxInstances(): ?int
    {
        return 1;
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return '<div class="limited-brick">Limited content</div>';
    }
}
