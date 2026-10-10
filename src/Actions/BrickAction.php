<?php

declare(strict_types=1);

namespace Awcodes\Mason\Actions;

use Awcodes\Mason\Mason;
use Awcodes\Mason\Support\BrickCommand;
use Awcodes\Mason\Support\BrickTree;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;

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
            ->modalHeading(function (array $arguments, Mason $component) {
                $brick = $component->getBrick($arguments['id']);

                if (blank($brick)) {
                    return null;
                }

                return $brick::getLabel();
            })
            ->modalWidth(Width::Large)
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
                        'preview' => base64_encode((string) $brick::toHtml($data)),
                        'outlineLabel' => $component->getBrickOutlineLabel($arguments['id'], $data),
                    ],
                ];

                $mode = $arguments['mode'] ?? 'insert';
                $state = $component->getState() ?? [];

                if (! is_array($state)) {
                    $state = [];
                }

                // Edit existing block, addressed by its path ("2.0") or its top level index
                $blockPath = $arguments['blockPath'] ?? $arguments['blockIndex'] ?? null;

                if ($mode === 'edit' && $blockPath !== null) {
                    $component->executeCommands([
                        BrickCommand::updateBrick(is_int($blockPath) ? $blockPath : (string) $blockPath, $brickContent),
                    ]);

                    return;
                }

                // Insert into a container brick, or at the top level without a parent
                $parent = filled($arguments['parent'] ?? null) ? (string) $arguments['parent'] : null;
                $position = $arguments['position'] ?? $arguments['dragPosition'] ?? null;

                if (filled($position) || $parent !== null) {
                    $component->executeCommands([
                        BrickCommand::insertBrick(
                            $brickContent,
                            filled($position) ? (int) $position : count(BrickTree::children($state, $parent)),
                            $parent,
                        ),
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
}
