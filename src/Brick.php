<?php

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

    public static function toHtml(array $config, array $data): ?string
    {
        return null;
    }

    public static function getPreviewLabel(array $config): string
    {
        return static::getLabel();
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return null;
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action->modalHidden();
    }
}
