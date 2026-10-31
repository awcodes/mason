<?php

declare(strict_types=1);

namespace Awcodes\Mason\Actions;

use Awcodes\Mason\Mason;
use Awcodes\Mason\Support\BrickCommand;
use Filament\Actions\Action;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Livewire\Component as LivewireComponent;

class BrickAction
{
    public const NAME = 'handleBrick';

    public static function make(): Action
    {
        return Action::make(static::NAME)
            ->bootUsing(function (Action $action, array $arguments, Mason $component): ?Action {
                $brick = $component->getBrick($arguments['id']);

                if (blank($brick)) {
                    return null;
                }

                return $brick::configureBrickAction($action);
            })
            ->fillForm(fn (array $arguments): ?array => $arguments['config'] ?? null)
            // A click on a part in the preview (mason_part()) opens the form at the component with that key
            ->afterFormFilled(function (Action $action, array $arguments, Mason $component, HasSchemas & LivewireComponent $livewire): void {
                $part = $arguments['part'] ?? null;

                if (! is_string($part) || blank($part)) {
                    return;
                }

                $schema = $livewire->getSchema("mountedActionSchema{$action->getNestingIndex()}");
                $target = $schema instanceof Schema ? static::findPart($schema, $part) : null;

                if (! $target) {
                    return;
                }

                $livewire->dispatch('mason-reveal-part', field: $component->getKey(), target: $target->getLivewireKey());
            })
            ->modalHeading(function (array $arguments, Mason $component) {
                $brick = $component->getBrick($arguments['id']);

                if (blank($brick)) {
                    return null;
                }

                return $brick::getLabel();
            })
            ->modalWidth(Width::Large)
            // Clicking outside the form would discard its changes; a brick can allow it in configureBrickAction()
            ->closeModalByClickingAway(false)
            ->modalSubmitActionLabel(fn (array $arguments): ?string => match ($arguments['mode']) {
                'insert' => __('mason::mason.actions.brick.modal.actions.insert.label'),
                'edit' => __('mason::mason.actions.brick.modal.actions.save.label'),
                default => null,
            })
            ->action(function (array $arguments, array $data, Mason $component): void {
                $brick = $component->getBrick($arguments['id']);

                if (blank($brick)) {
                    return;
                }

                $brickContent = [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'config' => $data,
                        'id' => $arguments['id'],
                        'label' => $brick::getLabel(),
                        'preview' => base64_encode($brick::toHtml($data)),
                        'outlineLabel' => $component->getBrickOutlineLabel($arguments['id'], $data),
                    ],
                ];

                $mode = $arguments['mode'] ?? 'insert';
                $state = $component->getState() ?? [];

                if (! is_array($state)) {
                    $state = [];
                }

                // Insert at the dragged position
                if (filled($arguments['dragPosition'] ?? null)) {
                    $position = (int) $arguments['dragPosition'];
                    $component->executeCommands([
                        BrickCommand::insertBrick($brickContent, $position),
                    ]);

                    return;
                }

                // Edit existing block
                if ($mode === 'edit' && isset($arguments['blockIndex'])) {
                    $index = (int) $arguments['blockIndex'];
                    $component->executeCommands([
                        BrickCommand::updateBrick($index, $brickContent),
                    ]);

                    return;
                }

                // Insert at the end (default for insert mode)
                $position = count($state);
                $component->executeCommands([
                    BrickCommand::insertBrick($brickContent, $position),
                ]);
            });
    }

    /**
     * The component or repeater item a part names: a component key, e.g. "heading", or a path through repeaters and
     * builders with the item's position, e.g. "cards.1.heading" or "cards.1"
     */
    protected static function findPart(Schema $schema, string $part): Component | Schema | null
    {
        $segments = explode('.', $part);
        $found = null;
        $schemas = [$schema];

        while ($segments !== []) {
            $segment = array_shift($segments);

            if ($found instanceof Component && ctype_digit($segment) && method_exists($found, 'getItems')) {
                $found = array_values($found->getItems())[(int) $segment] ?? null;
                $schemas = $found ? [$found] : [];

                continue;
            }

            $found = null;

            foreach ($schemas as $searchedSchema) {
                $found = $searchedSchema->getComponent(
                    fn (mixed $schemaComponent): bool => $schemaComponent instanceof Component
                        && $schemaComponent->getKey(isAbsolute: false) === $segment,
                );

                if ($found) {
                    break;
                }
            }

            if (! $found instanceof Component) {
                return null;
            }

            $schemas = $found->getChildSchemas();
        }

        return $found;
    }
}
