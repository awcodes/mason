<?php

namespace Awcodes\Mason;

use Awcodes\Mason\Actions\Section;
use Awcodes\Mason\Concerns\HasSidebar;
use Awcodes\Mason\Support\Helpers;
use Closure;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Contracts\CanBeLengthConstrained;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Filament\Support\Concerns\HasPlaceholder;
use Livewire\Component;

class Mason extends Field implements CanBeLengthConstrained
{
    use \Filament\Forms\Components\Concerns\CanBeLengthConstrained;
    use HasExtraAlpineAttributes;
    use HasExtraInputAttributes;
    use HasPlaceholder;
    use HasSidebar;

    protected string $view = 'mason::mason';

    protected bool | Closure $isJson = false;

    protected array | Closure | null $bricks = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (Mason $component, $state) {
            if (! $state) {
                return null;
            }

//            $state = Helpers::renderBricks($state, $component);

//            dd($state);

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

        $this->registerActions([
            fn () => $this->getBricks(),
        ]);
    }

    /**
     * @param  array<EditorCommand>  $commands
     * @param  array<string, mixed>  $editorSelection
     */
    public function runCommands(array $commands, array $editorSelection): void
    {
        $key = $this->getKey();
        $livewire = $this->getLivewire();

        $livewire->dispatch(
            'run-mason-commands',
            awaitMasonComponent: $key,
            livewireId: $livewire->getId(),
            key: $key,
            editorSelection: $editorSelection,
            commands: array_map(fn (EditorCommand $command): array => $command->toArray(), $commands),
        );
    }

    public function json(bool | Closure $condition = true): static
    {
        $this->isJson = $condition;

        return $this;
    }

    public function isJson(): bool
    {
        return (bool) $this->evaluate($this->isJson);
    }

    public function bricks(array | Closure $bricks): static
    {
        $this->bricks = $bricks;

        return $this;
    }

    public function getBricks(): array
    {
        return $this->evaluate($this->bricks) ?? [
            Section::make(),
        ];
    }
}
