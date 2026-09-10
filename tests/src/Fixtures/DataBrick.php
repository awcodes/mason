<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;

/**
 * Renders from the data passed alongside its config, so tests can assert that
 * render context reaches a brick.
 */
class DataBrick extends Brick
{
    public static function getId(): string
    {
        return 'data-brick';
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        $record = $data['record'] ?? null;

        if ($record === null) {
            return '<div class="data-brick">no record</div>';
        }

        return '<div class="data-brick">' . $record->title . '</div>';
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action->modalHidden();
    }
}
