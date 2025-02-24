<?php

namespace Awcodes\Mason\Actions;

use Awcodes\Mason\Mason;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Livewire\Component;

class InsertBrick
{
    public static function make(): Action
    {
        return Action::make('insertBrick')
            ->label(fn (): string => trans('mason::mason.insert_brick'))
            ->form(function (Mason $component) {
                return [
                    Select::make('name')
                        ->label('Brick')
                        ->placeholder('Select a brick')
                        ->options(function () use ($component) {
                            return collect($component->getBricks())->mapWithKeys(function ($brick) {
                                return [$brick->getName() => $brick->getLabel()];
                            });
                        })
                        ->searchable()
                        ->rules('required'),
                    ToggleButtons::make('position')
                        ->label('Position')
                        ->options([
                            'before' => 'Before',
                            'after' => 'After',
                        ])
                        ->grouped()
                        ->default('after')
                        ->rules('required'),
                ];
            })
            ->action(function (Mason $component, Component $livewire, array $data, array $arguments) {
                $data = [
                    ...$data,
                    'editorSelection' => $arguments['editorSelection'],
                    'statePath' => $component->getStatePath(),
                ];

                $livewire->dispatch('handle-brick-insert', ...$data);
            });
    }
}
