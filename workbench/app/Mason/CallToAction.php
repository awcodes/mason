<?php

declare(strict_types=1);

namespace Workbench\App\Mason;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Throwable;

/**
 * Demonstrates parts in a nested form: the button's fields sit in a collapsed
 * section of the second tab, and clicking the button in the preview opens both.
 */
class CallToAction extends Brick
{
    public static function getId(): string
    {
        return 'callToAction';
    }

    public static function getIcon(): string | Heroicon | Htmlable | null
    {
        return Heroicon::OutlinedCursorArrowRays;
    }

    public static function getOutlineLabel(array $config): ?string
    {
        return $config['heading'] ?? null;
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>|null  $data
     *
     * @throws Throwable
     */
    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.call-to-action', [
            'heading' => $config['heading'] ?? null,
            'text' => $config['text'] ?? null,
            'button_label' => $config['button_label'] ?? null,
            'button_url' => $config['button_url'] ?? null,
            'new_tab' => (bool) ($config['new_tab'] ?? false),
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->slideOver()
            ->schema([
                Tabs::make()->tabs([
                    Tab::make('Content')->key('content')->schema([
                        TextInput::make('heading')
                            ->required(),
                        Textarea::make('text'),
                    ]),
                    Tab::make('Button')->key('button')->schema([
                        Section::make('Link')
                            ->key('link')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                TextInput::make('button_label'),
                                TextInput::make('button_url')
                                    ->url(),
                                Toggle::make('new_tab')
                                    ->label('Open in a new tab'),
                            ]),
                    ]),
                ]),
            ]);
    }
}
