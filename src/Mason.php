<?php

namespace Awcodes\Mason;

use Awcodes\Mason\Actions\Batman;
use Awcodes\Mason\Actions\Section;
use Awcodes\Mason\Concerns\HasSidebar;
use Awcodes\Mason\EditorCommand;
use Closure;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Contracts\CanBeLengthConstrained;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Filament\Support\Concerns\HasPlaceholder;

class Mason extends Field implements CanBeLengthConstrained
{
    use \Filament\Forms\Components\Concerns\CanBeLengthConstrained;
    use HasExtraAlpineAttributes;
    use HasExtraInputAttributes;
    use HasPlaceholder;
    use HasSidebar;

    protected string $view = 'mason::mason';

    protected bool | Closure $isJson = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerActions([
            Batman::make(),
            Section::make(),
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
}
