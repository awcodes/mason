<?php

declare(strict_types=1);

namespace Awcodes\Mason;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

abstract class Brick
{
    abstract public static function getId(): string;

    public static function getLabel(): string
    {
        return (string) str(static::getId())
            ->kebab()
            ->replace('-', ' ')
            ->ucwords();
    }

    public static function getIcon(): string | Heroicon | Htmlable | null
    {
        return null;
    }

    /**
     * @return array<string>
     */
    public static function getTags(): array
    {
        return [];
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return null;
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action->modalHidden();
    }
}
