<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Width;

class ConfiguredBrick extends Brick
{
    public static function getId(): string
    {
        return 'configured-brick';
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return '<div>' . e($config['heading'] ?? '') . '</div>';
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->modalHeading('From the brick')
            ->modalWidth(Width::TwoExtraLarge)
            ->schema([
                TextInput::make('heading'),
            ]);
    }
}
