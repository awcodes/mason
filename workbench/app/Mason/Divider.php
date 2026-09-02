<?php

declare(strict_types=1);

namespace Workbench\App\Mason;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Throwable;

class Divider extends Brick
{
    public static function getId(): string
    {
        return 'divider';
    }

    public static function getIcon(): string | Heroicon | Htmlable | null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4 12h16"/></svg>');
    }

    /**
     * @return array<string>
     */
    public static function getTags(): array
    {
        return ['divider', 'rule', 'separator'];
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>|null  $data
     *
     * @throws Throwable
     */
    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.divider')->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action->modalHidden();
    }
}
