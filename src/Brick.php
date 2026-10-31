<?php

declare(strict_types=1);

namespace Awcodes\Mason;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Throwable;

abstract class Brick
{
    abstract public static function getId(): string;

    public static function getLabel(): string
    {
        return (string) str(static::getId())
            ->kebab()
            ->replace('-', ' ')
            ->ucwords();
    }

    public static function getIcon(): string | Heroicon | Htmlable | null
    {
        return null;
    }

    /**
     * @return array<string>
     */
    public static function getTags(): array
    {
        return [];
    }

    /**
     * How often the brick may be used in one Mason field, e.g. 1 for a hero. Null puts no limit on it.
     */
    public static function getMaxInstances(): ?int
    {
        return null;
    }

    /**
     * Distinguishes this brick from others of the same type in the editor outline.
     *
     * @param  array<string, mixed>  $config
     */
    public static function getOutlineLabel(array $config): ?string
    {
        return null;
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return null;
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action->modalHidden();
    }

    /**
     * Whether the brick has a form to edit. A brick without one hides its action's
     * modal, as this class does by default, and the editor shows no edit control for it.
     * The action is checked with the arguments the edit control mounts it with.
     */
    public static function isEditable(): bool
    {
        try {
            $action = Action::make('isEditable')
                ->arguments(['id' => static::getId(), 'mode' => 'edit']);

            return static::configureBrickAction($action)
                ->shouldOpenModal(checkForSchemaUsing: fn (): bool => true);
        } catch (Throwable) {
            // e.g. a modalHidden() closure that needs the mounted action; keep the edit control
            return true;
        }
    }
}
