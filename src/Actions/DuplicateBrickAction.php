<?php

declare(strict_types=1);

namespace Awcodes\Mason\Actions;

use Awcodes\Mason\Mason;
use Awcodes\Mason\Support\BrickCommand;
use Filament\Actions\Action;

class DuplicateBrickAction
{
    public const NAME = 'duplicateBrick';

    /**
     * Inserts a copy of the brick at the given index right after it. The copy's
     * config goes through the brick's duplicateConfig(), so the copy is rendered here.
     */
    public static function make(): Action
    {
        return Action::make(static::NAME)
            ->modalHidden()
            ->action(function (array $arguments, Mason $component): void {
                $state = $component->getState();
                $index = $arguments['index'] ?? null;

                if ($component->isDisabled() || ! is_array($state) || ! is_int($index) || ! isset($state[$index])) {
                    return;
                }

                $block = $state[$index];
                $id = $block['attrs']['id'] ?? null;
                $brick = is_string($id) ? $component->getBrick($id) : null;

                if (blank($brick)) {
                    return;
                }

                $config = $brick::duplicateConfig(
                    is_array($block['attrs']['config'] ?? null) ? $block['attrs']['config'] : [],
                );

                $block['attrs'] = [
                    ...$block['attrs'],
                    'config' => $config,
                    'label' => $brick::getLabel(),
                    'preview' => base64_encode((string) $brick::toHtml($config)),
                    'outlineLabel' => $component->getBrickOutlineLabel($id, $config),
                ];

                $component->executeCommands([
                    BrickCommand::insertBrick($block, $index + 1),
                ]);
            });
    }
}
