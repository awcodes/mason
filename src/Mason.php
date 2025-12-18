<?php

declare(strict_types=1);

namespace Awcodes\Mason;

use Awcodes\Mason\Actions\BrickAction;
use Awcodes\Mason\Concerns\HasBricks;
use Awcodes\Mason\Concerns\HasSidebar;
use Awcodes\Mason\Support\EditorCommand;
use Awcodes\Mason\Support\MasonRenderer;
use Closure;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Contracts\CanBeLengthConstrained;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Filament\Support\Concerns\HasPlaceholder;
use Livewire\Component;
use Tiptap\Editor;

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

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (Mason $component, $state) {
            if (! $state) {
                return null;
            }

            $state = $this->getTiptapEditor()->setContent($state)
                ->descendants(function (object &$node): void {
                    if ($node->type !== 'masonBrick') {
                        return;
                    }

                    $brick = $this->getBrick($node->attrs->id);

                    if (blank($brick)) {
                        return;
                    }

                    $nodeConfig = json_decode(json_encode($node->attrs->config ?? []), associative: true);

                    $node->attrs->label = $brick::getPreviewLabel($nodeConfig);
                    $node->attrs->preview = base64_encode($brick::toPreviewHtml($nodeConfig));
                })->getDocument();

            $component->state($state);
        });

        $this->afterStateUpdated(function (Mason $component, Component $livewire): void {
            $livewire->validateOnly($component->getStatePath());
        });

        $this->dehydrateStateUsing(function ($state) {
            if (! $state) {
                return null;
            }

            return $this->getTiptapEditor()->setContent($state)
                ->descendants(function (object &$node): void {
                    if ($node->type !== 'masonBrick') {
                        return;
                    }

                    unset($node->attrs->label);
                    unset($node->attrs->preview);
                })->getDocument();
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

    public function getTiptapEditor(): Editor
    {
        return MasonRenderer::make()->getEditor();
    }
}
