<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

class TestBrick extends Brick
{
    public static function getId(): string
    {
        return 'test-brick';
    }

    public static function getIcon(): ?string
    {
        return 'heroicon-o-star';
    }

    public static function getTags(): array
    {
        return ['hero', 'banner', 'marketing'];
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        $title = $config['title'] ?? '';
        $content = $config['content'] ?? '';

        return "<div class=\"test-brick\"><h2>{$title}</h2><p>{$content}</p></div>";
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->schema([
                TextInput::make('title'),
                TextInput::make('content'),
            ]);
    }
}
