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

    /**
     * @var array<string, mixed> | Closure
     */
    protected array | Closure $previewData = [];

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

            if ($component->hasOutline()) {
                $state = array_map(function (mixed $block) use ($component): mixed {
                    if (! is_array($block) || ($block['type'] ?? null) !== 'masonBrick') {
                        return $block;
                    }

                    $block['attrs']['outlineLabel'] = $component->getBrickOutlineLabel(
                        $block['attrs']['id'] ?? '',
                        is_array($block['attrs']['config'] ?? null) ? $block['attrs']['config'] : [],
                    );

                    return $block;
                }, $state);
            }

            $component->state($state);
        });

        // The editor stops inserts at the limit, but its state comes from the browser
        $this->rule(fn (Mason $component): Closure => function (string $attribute, mixed $value, Closure $fail) use ($component): void {
            if (! is_array($value)) {
                return;
            }

            foreach ($component->getBrickLimits() as $id => $max) {
                if ($component->countBrickInstances($id, $value) > $max) {
                    $fail(trans_choice('mason::mason.max_instances.exceeded', $max, [
                        'label' => $component->getBrick($id)::getLabel(),
                        'max' => $max,
                    ]));
                }
            }
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
                unset($block['attrs']['outlineLabel']);

                return $block;
            }, $state);
        });
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function getBrickOutlineLabel(string $id, array $config): ?string
    {
        $brick = $this->getBrick($id);

        if (! $this->hasOutline() || blank($brick)) {
            return null;
        }

        $label = $brick::getOutlineLabel($config);

        return filled($label) ? $label : null;
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
        return RenderContext::encode($this->getFlatBricks(), $this->getPreviewLayout(), $this->getPreviewData());
    }

    /**
     * Data the bricks get in the editor preview as toHtml()'s $data, e.g. the language of the record being edited.
     * Without it they get null. It travels signed, not encrypted, so it must be JSON and hold nothing secret.
     *
     * @param  array<string, mixed> | Closure  $data
     */
    public function previewData(array | Closure $data): static
    {
        $this->previewData = $data;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPreviewData(): array
    {
        $data = $this->evaluate($this->previewData);

        return is_array($data) ? $data : [];
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
     * @param  array<int, mixed> | null  $state  the field's state if omitted
     */
    public function countBrickInstances(string $id, ?array $state = null): int
    {
        $state ??= $this->getState();

        if (! is_array($state)) {
            return 0;
        }

        return count(array_filter(
            $state,
            fn (mixed $block): bool => is_array($block)
                && ($block['type'] ?? null) === 'masonBrick'
                && ($block['attrs']['id'] ?? null) === $id,
        ));
    }

    /**
     * Whether one more of the brick would go over its getMaxInstances()
     *
     * @param  array<int, mixed> | null  $state  the field's state if omitted
     */
    public function hasReachedMaxInstances(string $id, ?array $state = null): bool
    {
        $brick = $this->getBrick($id);
        $max = filled($brick) ? $brick::getMaxInstances() : null;

        return $max !== null && $this->countBrickInstances($id, $state) >= $max;
    }

    /**
     * Limits of the bricks that have one, for the editor
     *
     * @return array<string, int>
     */
    public function getBrickLimits(): array
    {
        $limits = [];

        foreach ($this->getFlatBricks() as $brick) {
            if (($max = $brick::getMaxInstances()) !== null) {
                $limits[$brick::getId()] = $max;
            }
        }

        return $limits;
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

        if (! $brick || $this->hasReachedMaxInstances($brick['attrs']['id'] ?? '', $state)) {
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
