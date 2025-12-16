<?php

namespace Awcodes\Mason;

use Awcodes\Mason\Actions\BrickAction;
use Awcodes\Mason\Actions\InsertBrick;
use Awcodes\Mason\Concerns\HasBricks;
use Awcodes\Mason\Concerns\HasSidebar;
use Awcodes\Mason\Support\Helpers;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Contracts\CanBeLengthConstrained;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Filament\Support\Concerns\HasPlaceholder;
use Livewire\Component;

class Mason extends Field implements CanBeLengthConstrained
{
    use \Filament\Forms\Components\Concerns\CanBeLengthConstrained;
    use HasBricks;
    use HasExtraAlpineAttributes;
    use HasExtraInputAttributes;
    use HasPlaceholder;
    use HasSidebar;

    protected string $view = 'mason::mason';

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (Mason $component, $state) {
            if (! $state) {
                return null;
            }

            $component->state($state);
        });

        $this->afterStateUpdated(function (Mason $component, Component $livewire): void {
            $livewire->validateOnly($component->getStatePath());
        });

        $this->dehydrateStateUsing(function ($state) {
            if (! $state) {
                return null;
            }

            return Helpers::sanitizeBricks($state);
        });
    }

    public function getDefaultActions(): array
    {
        return [
            BrickAction::make(),
        ];
    }

    /**
     * @param  array<EditorCommand>  $commands
     * @param  ?array<string, mixed>  $editorSelection
     */
    public function runCommands(array $commands, ?array $editorSelection = null): void
    {
        $key = $this->getKey();
        $livewire = $this->getLivewire();

        /** @phpstan-ignore-next-line  */
        $livewire->dispatch(
            event: 'run-mason-commands',
            awaitMasonComponent: $key,
            /** @phpstan-ignore-next-line  */
            livewireId: $livewire->getId(),
            key: $key,
            editorSelection: $editorSelection,
            commands: array_map(fn (EditorCommand $command): array => $command->toArray(), $commands),
        );
    }
}
