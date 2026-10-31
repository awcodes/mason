<?php

declare(strict_types=1);

namespace Awcodes\Mason\Tests\Fixtures;

use Awcodes\Mason\Brick;
use Awcodes\Mason\Mason;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use RuntimeException;

class PartsBrick extends Brick
{
    public static ?Closure $afterFormFilled = null;

    public static function getId(): string
    {
        return 'parts-brick';
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        if ($config['fail'] ?? false) {
            throw new RuntimeException('Rendering failed');
        }

        $preview = Mason::isRenderingPreview() ? 'yes' : 'no';

        return '<section ' . mason_part('design') . " data-preview=\"{$preview}\"><h2 " . mason_part('heading') . '>Heading</h2></section>';
    }

    public static function configureBrickAction(Action $action): Action
    {
        if (static::$afterFormFilled) {
            $action->afterFormFilled(static::$afterFormFilled);
        }

        return $action->schema([
            Tabs::make()->tabs([
                Tab::make('Content')->key('content')->schema([
                    TextInput::make('heading'),
                    TextInput::make('secret')->hidden(),
                    Repeater::make('items')->schema([
                        TextInput::make('title'),
                    ]),
                ]),
                Tab::make('Design')->key('design')->schema([
                    TextInput::make('color'),
                    Section::make('Advanced')->key('advanced')->collapsed()->schema([
                        TextInput::make('anchor'),
                    ]),
                ]),
            ]),
        ]);
    }
}
