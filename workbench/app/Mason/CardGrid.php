<?php

declare(strict_types=1);

namespace Workbench\App\Mason;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Throwable;

class CardGrid extends Brick
{
    public static function getId(): string
    {
        return 'cardGrid';
    }

    public static function getLabel(): string
    {
        return 'Card Grid';
    }

    public static function getIcon(): string | Heroicon | Htmlable | null
    {
        return Heroicon::OutlinedSquares2x2;
    }

    /**
     * @return array<string>
     */
    public static function getTags(): array
    {
        return ['cards', 'grid', 'features', 'columns'];
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>|null  $data
     *
     * @throws Throwable
     */
    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.card-grid', [
            'cards' => $config['cards'] ?? [],
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->slideOver()
            ->fillForm(fn (array $arguments): array => [
                'cards' => $arguments['config']['cards'] ?? [],
            ])
            ->schema([
                Repeater::make('cards')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['heading'] ?? null)
                    ->schema([
                        TextInput::make('heading')
                            ->live(onBlur: true)
                            ->required(),
                        RichEditor::make('body'),
                    ]),
            ]);
    }
}
