<?php

namespace Awcodes\Mason\Actions;

use Awcodes\Mason\EditorCommand;
use Awcodes\Mason\Mason;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section as FilamentSection;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Support\Js;

class Section
{
    public static function make(): Action
    {
        return Action::make('section')
            ->label('Section')
            ->modalHeading('Section Settings')
            ->slideOver()
            ->fillForm(fn (array $arguments): array => [
                'background_color' => $arguments['background_color'] ?? 'white',
                'image_position' => $arguments['image_position'] ?? null,
                'image_alignment' => $arguments['image_alignment'] ?? null,
                'image_flush' => $arguments['image_flush'] ?? null,
                'image_rounded' => $arguments['image_rounded'] ?? null,
                'image_shadow' => $arguments['image_shadow'] ?? null,
                'text' => $arguments['text'] ?? null,
                'image' => $arguments['image'] ?? null,
                'actions' => [],
                'actions_alignment' => null,
            ])
            ->form([
                Radio::make('background_color')
                    ->options([
                        'white' => 'White',
                        'gray' => 'Gray',
                        'primary' => 'primary',
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
                            ToggleButtons::make('image_flush')
                                ->options([
                                    false => 'No',
                                    true => 'Yes',
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
            ->alpineClickHandler(fn (Mason $component): string => '$wire.mountFormComponentAction(\'' . $component->getStatePath() . '\', \'section\', { ...getEditor().getAttributes(\'section\'), editorSelection }, ' . Js::from(['schemaComponent' => $component->getKey()]) . ')')
            ->action(function (array $arguments, array $data, Mason $component) {
                $component->runCommands(
                    [
                        new EditorCommand(
                            name: 'setBrick',
                            arguments: [[
                                'identifier' => 'section',
                                'values' => $data,
                                'view' => view('mason::bricks.section', $data)->toHtml(),
                            ]],
                        ),
                    ],
                    editorSelection: $arguments['editorSelection'],
                );
            });
    }
}
