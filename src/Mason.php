<?php

declare(strict_types=1);

namespace Awcodes\Mason;

use Awcodes\Mason\Actions\BrickAction;
use Awcodes\Mason\Concerns\HasBricks;
use Awcodes\Mason\Concerns\HasSidebar;
use Awcodes\Mason\Support\BrickCommand;
use Awcodes\Mason\Support\RenderContext;
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
    use HasBricks;
    use HasExtraAlpineAttributes;
    use HasExtraInputAttributes;
    use HasPlaceholder;
    use HasSidebar;

    protected string $view = 'mason::mason';

    protected bool | Closure | null $isJson = null;

    protected bool | Closure | null $shouldDblClickToEdit = null;

    protected string | Closure | null $previewLayout = null;

    protected string | Closure | null $defaultColorMode = null;

    protected bool | Closure $showColorModeToggle = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (Mason $component, $state) {
            if (! $state) {
                return null;
            }

            // Ensure the state is an array
            if (! is_array($state)) {
                $state = [];
            }

            if (array_key_exists('content', $state)) {
                $state = $state['content'];
            }

            $component->state($state);
        });

        $this->afterStateUpdated(function (Mason $component, Component $livewire): void {
            $livewire->validateOnly($component->getStatePath());
        });

        $this->dehydrateStateUsing(function ($state): ?array {
            if (! $state || ! is_array($state)) {
                return null;
            }

            // Remove preview data before saving
            return array_map(function (array $block): array {
                if (($block['type'] ?? null) !== 'masonBrick') {
                    return $block;
                }

                unset($block['attrs']['label']);
                unset($block['attrs']['preview']);

                return $block;
            }, $state);
        });
    }

    public function getDefaultActions(): array
    {
        return [
            BrickAction::make(),
        ];
    }

    /**
     * Execute block commands on the current state
     *
     * @param  array<BrickCommand>  $commands
     * @return array<int, array<string, mixed>>
     */
    public function executeCommands(array $commands): array
    {
        $state = $this->getState() ?? [];

        if (! is_array($state)) {
            $state = [];
        }

        foreach ($commands as $command) {
            $state = match ($command->name) {
                'insertBrick' => $this->executeInsertBrick($state, $command->arguments),
                'updateBrick' => $this->executeUpdateBrick($state, $command->arguments),
                'deleteBrick' => $this->executeDeleteBrick($state, $command->arguments),
                'moveBrick' => $this->executeMoveBrick($state, $command->arguments),
                default => $state,
            };
        }

        $this->state($state);

        return $state;
    }

    public function doubleClickToEdit(bool | Closure $condition = true): static
    {
        $this->shouldDblClickToEdit = $condition;

        return $this;
    }

    public function shouldDblClickToEdit(): bool
    {
        return $this->evaluate($this->shouldDblClickToEdit) ?? false;
    }

    public function previewLayout(string | Closure | null $layout): static
    {
        $this->previewLayout = $layout;

        return $this;
    }

    public function getPreviewLayout(): ?string
    {
        return $this->evaluate($this->previewLayout) ?? config('mason.preview.layout');
    }

    /**
     * The preview renders inside an iframe fed by a separate request, so the
     * bricks and layout it may use travel as a signed payload.
     */
    public function getRenderContext(): string
    {
        return RenderContext::encode($this->getFlatBricks(), $this->getPreviewLayout());
    }

    public function defaultColorMode(string | Closure | null $mode): static
    {
        $this->defaultColorMode = $mode;

        return $this;
    }

    public function getDefaultColorMode(): string
    {
        return $this->evaluate($this->defaultColorMode) ?? 'light';
    }

    public function colorModeToggle(bool | Closure $condition = true): static
    {
        $this->showColorModeToggle = $condition;

        return $this;
    }

    public function hasColorModeToggle(): bool
    {
        return (bool) $this->evaluate($this->showColorModeToggle);
    }

    /**
     * @param  array<int, array<string, mixed>>  $state
     * @param  array<string, mixed>  $arguments
     * @return array<int, array<string, mixed>>
     */
    protected function executeInsertBrick(array $state, array $arguments): array
    {
        $brick = $arguments['brick'] ?? null;
        $position = $arguments['position'] ?? count($state);

        if (! $brick) {
            return $state;
        }

        array_splice($state, $position, 0, [$brick]);

        return $state;
    }

    /**
     * @param  array<int, array<string, mixed>>  $state
     * @param  array<string, mixed>  $arguments
     * @return array<int, array<string, mixed>>
     */
    protected function executeUpdateBrick(array $state, array $arguments): array
    {
        $index = $arguments['index'] ?? null;
        $brick = $arguments['brick'] ?? null;

        if ($index === null || ! isset($state[$index]) || ! $brick) {
            return $state;
        }

        $state[$index] = $brick;

        return $state;
    }

    /**
     * @param  array<int, array<string, mixed>>  $state
     * @param  array<string, mixed>  $arguments
     * @return array<int, array<string, mixed>>
     */
    protected function executeDeleteBrick(array $state, array $arguments): array
    {
        $index = $arguments['index'] ?? null;

        if ($index === null || ! isset($state[$index])) {
            return $state;
        }

        array_splice($state, $index, 1);

        return $state;
    }

    /**
     * @param  array<int, array<string, mixed>>  $state
     * @param  array<string, mixed>  $arguments
     * @return array<int, array<string, mixed>>
     */
    protected function executeMoveBrick(array $state, array $arguments): array
    {
        $from = $arguments['from'] ?? null;
        $to = $arguments['to'] ?? null;

        if ($from === null || $to === null || ! isset($state[$from])) {
            return $state;
        }

        $moved = array_splice($state, $from, 1);
        array_splice($state, $to, 0, $moved);

        return $state;
    }
}
