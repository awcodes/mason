<?php

declare(strict_types=1);

namespace Awcodes\Mason;

use Awcodes\Mason\Actions\BrickAction;
use Awcodes\Mason\Concerns\HasBricks;
use Awcodes\Mason\Concerns\HasSidebar;
use Awcodes\Mason\Support\BrickCommand;
use Awcodes\Mason\Support\BrickTree;
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

            if ($component->hasOutline()) {
                $state = BrickTree::map($state, function (array $block) use ($component): array {
                    if (($block['type'] ?? null) !== 'masonBrick') {
                        return $block;
                    }

                    $block['attrs']['outlineLabel'] = $component->getBrickOutlineLabel(
                        $block['attrs']['id'] ?? '',
                        is_array($block['attrs']['config'] ?? null) ? $block['attrs']['config'] : [],
                    );

                    return $block;
                });
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
            return BrickTree::map($state, function (array $block): array {
                if (($block['type'] ?? null) !== 'masonBrick') {
                    return $block;
                }

                unset($block['attrs']['label']);
                unset($block['attrs']['preview']);
                unset($block['attrs']['outlineLabel']);

                return $block;
            });
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
     * The brick at the path, or null for the top level and paths that don't exist.
     *
     * @return array<string, mixed>|null
     */
    public function getBrickAt(string | int | null $path): ?array
    {
        $state = $this->getState();

        return is_array($state) ? BrickTree::get($state, $path) : null;
    }

    /**
     * Whether a brick may be placed in the brick at the path (null for the top level).
     */
    public function canInsertBrickInto(string | int | null $parent, string $brickId): bool
    {
        if (BrickTree::parse($parent) === []) {
            return $this->canContainBrick(null, $brickId);
        }

        $container = $this->getBrickAt($parent);

        return $container !== null && $this->canContainBrick($container['attrs']['id'] ?? null, $brickId);
    }

    /**
     * Positions are indexes at the top level, or paths like "2.0" for bricks inside container bricks.
     *
     * @param  array<int, array<string, mixed>>  $state
     * @param  array<string, mixed>  $arguments
     * @return array<int, array<string, mixed>>
     */
    protected function executeInsertBrick(array $state, array $arguments): array
    {
        $brick = $arguments['brick'] ?? null;
        $parent = $arguments['parent'] ?? null;
        $position = $arguments['position'] ?? null;

        if (! $brick) {
            return $state;
        }

        if (BrickTree::parse($parent) !== []) {
            $container = BrickTree::get($state, $parent);

            if ($container === null || ! $this->canContainBrick($container['attrs']['id'] ?? null, (string) ($brick['attrs']['id'] ?? ''))) {
                return $state;
            }
        }

        return BrickTree::insert($state, $parent, $position === null ? null : (int) $position, $brick);
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

        if ($index === null || ! $brick) {
            return $state;
        }

        $current = BrickTree::get($state, $index);

        if ($current === null) {
            return $state;
        }

        // Editing a container brick changes its settings, not the bricks inside it
        if (! array_key_exists('content', $brick) && array_key_exists('content', $current)) {
            $brick['content'] = $current['content'];
        }

        // Nor the attributes set from elsewhere, e.g. layout data its container keeps on it
        if (is_array($current['attrs'] ?? null) && is_array($brick['attrs'] ?? null)) {
            $brick['attrs'] = [...$current['attrs'], ...$brick['attrs']];
        }

        return BrickTree::update($state, $index, $brick);
    }

    /**
     * @param  array<int, array<string, mixed>>  $state
     * @param  array<string, mixed>  $arguments
     * @return array<int, array<string, mixed>>
     */
    protected function executeDeleteBrick(array $state, array $arguments): array
    {
        $index = $arguments['index'] ?? null;

        if ($index === null) {
            return $state;
        }

        return BrickTree::remove($state, $index);
    }

    /**
     * Without a parent the brick moves among its siblings, and "to" is its index afterwards.
     *
     * @param  array<int, array<string, mixed>>  $state
     * @param  array<string, mixed>  $arguments
     * @return array<int, array<string, mixed>>
     */
    protected function executeMoveBrick(array $state, array $arguments): array
    {
        $from = $arguments['from'] ?? null;
        $to = $arguments['to'] ?? null;

        if ($from === null || $to === null || BrickTree::get($state, $from) === null) {
            return $state;
        }

        $fromPath = BrickTree::parse($from);
        $parent = array_key_exists('parent', $arguments) ? $arguments['parent'] : array_slice($fromPath, 0, -1);

        if (BrickTree::parse($parent) !== []) {
            $container = BrickTree::get($state, $parent);
            $brick = BrickTree::get($state, $from);

            if ($container === null || ! $this->canContainBrick($container['attrs']['id'] ?? null, (string) ($brick['attrs']['id'] ?? ''))) {
                return $state;
            }
        }

        return BrickTree::move($state, $fromPath, $parent, (int) $to);
    }
}
