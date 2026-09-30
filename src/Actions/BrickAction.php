<?php

declare(strict_types=1);

namespace Awcodes\Mason\Actions;

use Awcodes\Mason\Mason;
use Awcodes\Mason\Support\BrickCommand;
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
}
