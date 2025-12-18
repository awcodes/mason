<?php

declare(strict_types=1);

namespace Awcodes\Mason\Bricks;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section as FilamentSection;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Throwable;

class Section extends Brick
{
    public static function getId(): string
    {
        return 'section';
    }

    public static function getIcon(): string | Heroicon | Htmlable | null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 20h.01M4 20h.01M8 20h.01M12 20h.01M16 20h.01M20 4h.01M4 4h.01M8 4h.01M12 4h.01M16 4v.01M4 9a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/></svg>');
    }

    /**
     * @throws Throwable
     */
    public static function toPreviewHtml(array $config): ?string
    {
        return static::toHtml($config, []);
    }

    /**
     * @throws Throwable
     */
    public static function toHtml(array $config, array $data): ?string
    {
        return view('mason::bricks.section.index', [
            'background_color' => $config['background_color'] ?? 'white',
            'image_position' => $config['image_position'] ?? null,
            'image_alignment' => $config['image_alignment'] ?? null,
            'image_rounded' => $config['image_rounded'] ?? false,
            'image_shadow' => $config['image_shadow'] ?? false,
            'text' => $config['text'] ?? null,
            'image' => $config['image'] ?? null,
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->slideOver()
            ->schema([
                Radio::make('background_color')
                    ->options([
                        'white' => 'White',
                        'gray' => 'Gray',
                        'primary' => 'Primary',
                    ])
                    ->inline()
                    ->inlineLabel(false),
                FileUpload::make('image'),
                RichEditor::make('text'),
                FilamentSection::make('Variants')
                    ->schema([
                        Grid::make()->schema([
                            ToggleButtons::make('image_position')
                                ->options([
                                    'start' => 'Start',
                                    'end' => 'End',
                                ])
                                ->grouped(),
                            ToggleButtons::make('image_alignment')
                                ->options([
                                    'top' => 'Top',
                                    'middle' => 'Middle',
                                    'bottom' => 'Bottom',
                                ])
                                ->grouped(),
                            ToggleButtons::make('image_rounded')
                                ->options([
                                    false => 'No',
                                    true => 'Yes',
                                ])
                                ->grouped(),
                            ToggleButtons::make('image_shadow')
                                ->options([
                                    false => 'No',
                                    true => 'Yes',
                                ])
                                ->grouped(),
                        ]),
                    ]),
            ]);
    }
}
