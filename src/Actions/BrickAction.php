<?php

namespace Awcodes\Mason\Actions;

use Awcodes\Mason\Mason;
use Awcodes\Mason\Support\EditorCommand;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;

class BrickAction
{
    public const NAME = 'handleBrick';

    public static function make(): Action
    {
        return Action::make(static::NAME)
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
            ->bootUsing(function (Action $action, array $arguments, Mason $component) {
                $brick = $component->getBrick($arguments['id']);

                if (blank($brick)) {
                    return;
                }

                return $brick::configureBrickAction($action);
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
                        'label' => $brick::getPreviewLabel($data),
                        'preview' => base64_encode($brick::toPreviewHtml($data)),
                    ],
                ];

                // Insert at the dragged position
                if (filled($arguments['dragPosition'] ?? null)) {
                    $component->runCommands(
                        [
                            EditorCommand::make(
                                'insertContentAt',
                                arguments: [
                                    $arguments['dragPosition'],
                                    $brickContent,
                                ],
                            ),
                        ],
                    );

                    return;
                }

                // Insert after the currently selected node
                if (
                    ($arguments['editorSelection']['type'] === 'node') &&
                    (($arguments['mode'] ?? null) === 'insert')
                ) {
                    $component->runCommands(
                        [
                            EditorCommand::make(
                                'insertContentAt',
                                arguments: [
                                    ($arguments['editorSelection']['anchor'] ?? -1) + 1,
                                    $brickContent,
                                ],
                            ),
                        ],
                    );

                    return;
                }

                // Fixes an issue where the editor selection is sent as text instead of a node,
                // which causes the block update to fail when though the block is selected.
                if (
                    (($arguments['mode'] ?? null) === 'edit') &&
                    ($arguments['editorSelection']['type'] !== 'node')
                ) {
                    $arguments['editorSelection']['type'] = 'node';
                    $arguments['editorSelection']['anchor']--;

                    unset($arguments['editorSelection']['head']);
                }

                // Insert at the current selection
                $component->runCommands(
                    [
                        EditorCommand::make(
                            'insertContent',
                            arguments: [
                                $brickContent,
                            ],
                        ),
                    ],
                    editorSelection: $arguments['editorSelection'],
                );
            });
    }
}
