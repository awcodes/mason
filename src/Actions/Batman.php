<?php

namespace Awcodes\Mason\Actions;

use Awcodes\Mason\EditorCommand;
use Awcodes\Mason\Mason;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Js;

class Batman
{
    public static function make(): Action
    {
        return Action::make('batman')
            ->label('Batman')
            ->modalHeading('Batman Settings')
            ->slideOver()
            ->fillForm(fn (array $arguments): array => [
                'name' => 'Batman',
                'color' => 'black',
                'side' => 'hero',
            ])
            ->form([
                Radio::make('name')
                    ->options([
                        'Batman' => 'Batman',
                        'Robin' => 'Robin',
                        'Joker' => 'Joker',
                        'Poison Ivy' => 'Poison Ivy',
                        'Harley Quinn' => 'Harley Quinn',
                    ])
                    ->inline()
                    ->inlineLabel(false),
                Radio::make('color')
                    ->options([
                        'black' => 'black',
                        'yellow' => 'yellow',
                        'purple' => 'purple',
                        'green' => 'green',
                        'red' => 'red',
                    ])
                    ->inline()
                    ->inlineLabel(false),
                Radio::make('side')
                    ->options([
                        'hero' => 'Hero',
                        'villain' => 'Villain',
                    ])
                    ->inline()
                    ->inlineLabel(false),
            ])
            ->alpineClickHandler(fn (Mason $component): string => '$wire.mountFormComponentAction(\'' . $component->getStatePath() . '\', \'batman\', { ...getEditor().getAttributes(\'batman\'), editorSelection }, ' . Js::from(['schemaComponent' => $component->getKey()]) . ')')
            ->action(function (array $arguments, array $data, Mason $component) {
                $component->runCommands(
                    [
                        new EditorCommand(
                            name: 'setBrick',
                            arguments: [[
                                'identifier' => 'batman',
                                'values' => [
                                    'name' => $data['name'],
                                    'color' => $data['color'],
                                    'side' => $data['side'],
                                ],
                                'view' => view('mason::bricks.batman', $data)->toHtml(),
                            ]],
                        ),
                    ],
                    editorSelection: $arguments['editorSelection'],
                );
            });
    }
}
