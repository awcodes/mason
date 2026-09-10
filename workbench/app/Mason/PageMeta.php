<?php

declare(strict_types=1);

namespace Workbench\App\Mason;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Throwable;

/**
 * Renders from the record the content belongs to rather than from its own
 * config, so it demonstrates the data passed alongside $config to toHtml().
 *
 * The front end supplies the record explicitly (see pages/show.blade.php) and
 * MasonEntry supplies its own; the editor preview has no record in scope, so
 * the view falls back to a placeholder there.
 */
class PageMeta extends Brick
{
    public static function getId(): string
    {
        return 'pageMeta';
    }

    public static function getIcon(): string | Heroicon | Htmlable | null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h18v14H3zM7 9h4M7 13h10M7 16h6"/></svg>');
    }

    /**
     * @return array<string>
     */
    public static function getTags(): array
    {
        return ['meta', 'record', 'byline', 'page', 'details'];
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>|null  $data
     *
     * @throws Throwable
     */
    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.page-meta', [
            'heading' => $config['heading'] ?? 'About this page',
            'show_url' => $config['show_url'] ?? true,
            // Absent in the editor, where no record is in scope.
            'record' => $data['record'] ?? null,
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->schema([
                TextInput::make('heading')
                    ->default('About this page')
                    ->helperText('The rest of this brick is read from the page itself.'),
                ToggleButtons::make('show_url')
                    ->label('Show the page URL')
                    ->boolean()
                    ->default(true)
                    ->grouped(),
            ]);
    }
}
