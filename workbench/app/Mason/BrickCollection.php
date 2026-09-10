<?php

declare(strict_types=1);

namespace Workbench\App\Mason;

use Awcodes\Mason\BrickGroup;
use Awcodes\Mason\Bricks\Section;

class BrickCollection
{
    /**
     * @return array<int, class-string<\Awcodes\Mason\Brick> | BrickGroup>
     */
    public static function make(): array
    {
        return [
            BrickGroup::make('Content')
                ->bricks([
                    Hero::class,
                    CardGrid::class,
                    PageMeta::class,
                ]),
            Section::class,
            Divider::class,
        ];
    }
}
