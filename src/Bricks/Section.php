<?php

namespace Awcodes\Mason\Bricks;

use Awcodes\Mason\Brick;
use Awcodes\Mason\EditorCommand;
use Awcodes\Mason\Mason;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section as FilamentSection;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\HtmlString;

class Section
{
    public static function make(): Brick
    {
        return Brick::make('section')
            ->label('Section')
            ->modalHeading('Section Settings')
            ->icon(new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 20h.01M4 20h.01M8 20h.01M12 20h.01M16 20h.01M20 4h.01M4 4h.01M8 4h.01M12 4h.01M16 4v.01M4 9a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/></svg>'))
            ->slideOver()
            ->fillForm(fn (array $arguments): array => [
                'background_color' => $arguments['background_color'] ?? 'white',
                'image_position' => $arguments['image_position'] ?? null,
                'image_alignment' => $arguments['image_alignment'] ?? null,
                'image_rounded' => $arguments['image_rounded'] ?? null,
                'image_shadow' => $arguments['image_shadow'] ?? null,
                'text' => $arguments['text'] ?? null,
                'image' => $arguments['image'] ?? null,
            ])
            ->form([
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
                        Grid::make(3)->schema([
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
            ])
            ->action(function (array $arguments, array $data, Mason $component) {
                $component->runCommands(
                    [
                        new EditorCommand(
                            name: 'setBrick',
                            arguments: [[
                                'identifier' => 'section',
                                'values' => $data,
                                'path' => 'mason::bricks.section',
                                'view' => view('mason::bricks.section', $data)->toHtml(),
                            ]],
                        ),
                    ],
                    editorSelection: $arguments['editorSelection'],
                );
            });
    }
}
