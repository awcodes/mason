<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\Pages\Pages;

use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Workbench\App\Filament\Resources\Pages\PageResource;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    /** @return array<\Filament\Actions\Action> */
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
