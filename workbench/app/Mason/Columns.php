<?php

declare(strict_types=1);

namespace Workbench\App\Mason;

use Awcodes\Mason\Brick;
use Awcodes\Mason\Contracts\ContainsBricks;
use Awcodes\Mason\Support\BrickChildren;
use Filament\Actions\Action;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Throwable;

/**
 * A container brick: Mason renders the bricks inside it, the brick only lays them out.
 */
class Columns extends Brick implements ContainsBricks
{
    public static function getId(): string
    {
        return 'columns';
    }

    public static function getIcon(): string | Heroicon | Htmlable | null
    {
        return Heroicon::OutlinedViewColumns;
    }

    public static function getOutlineLabel(array $config): ?string
    {
        return ($config['columns'] ?? 2) . ' columns';
    }

    public static function getAllowedChildren(): ?array
    {
        return null;
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>|null  $data
     *
     * @throws Throwable
     */
    public static function toHtmlWithChildren(array $config, BrickChildren $children, ?array $data = null): ?string
    {
        return view('mason.columns', [
            'columns' => (int) ($config['columns'] ?? 2),
            'children' => $children,
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->fillForm(fn (array $arguments): array => $arguments['config'] ?? ['columns' => 2])
            ->schema([
                ToggleButtons::make('columns')
                    ->options([2 => '2', 3 => '3', 4 => '4'])
                    ->inline()
                    ->required(),
            ]);
    }
}
