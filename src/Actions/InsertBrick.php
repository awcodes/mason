<?php

namespace Awcodes\Mason\Actions;

use Awcodes\Mason\Mason;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Livewire\Component;

class InsertBrick
{
    public static function make(): Action
    {
        return Action::make('insertBrick')
            ->label(fn (): string => trans('mason::mason.insert_brick'))
            ->modalWidth(Width::Small)
            ->modalFooterActionsAlignment(Alignment::Center)
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
                    Radio::make('position')
                        ->label('Position')
                        ->options([
                            'before' => 'Before',
                            'after' => 'After',
                        ])
                        ->inline()
                        ->default('after')
                        ->rules('required')
                        ->extraAttributes(['style' => 'margin-bottom: 1rem;']),
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
