<?php

declare(strict_types=1);

namespace Workbench\App\Mason;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Throwable;

class Hero extends Brick
{
    public static function getId(): string
    {
        return 'hero';
    }

    public static function getIcon(): string | Heroicon | Htmlable | null
    {
        return Heroicon::OutlinedMegaphone;
    }

    /**
     * @return array<string>
     */
    public static function getTags(): array
    {
        return ['hero', 'banner', 'header', 'landing page', 'marketing'];
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>|null  $data
     *
     * @throws Throwable
     */
    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.hero', [
            'background_color' => $config['background_color'] ?? 'white',
            'heading' => $config['heading'] ?? null,
            'text' => $config['text'] ?? null,
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->slideOver()
            ->fillForm(fn (array $arguments): array => [
                'background_color' => $arguments['config']['background_color'] ?? 'white',
                'heading' => $arguments['config']['heading'] ?? null,
                'text' => $arguments['config']['text'] ?? null,
            ])
            ->schema([
                Radio::make('background_color')
                    ->options([
                        'white' => 'White',
                        'gray' => 'Gray',
                        'primary' => 'Primary',
                    ])
                    ->inline()
                    ->inlineLabel(false),
                TextInput::make('heading')
                    ->required(),
                RichEditor::make('text'),
            ]);
    }
}
