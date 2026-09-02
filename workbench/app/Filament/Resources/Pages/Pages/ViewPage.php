<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\Pages\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Workbench\App\Filament\Resources\Pages\PageResource;

class ViewPage extends ViewRecord
{
    protected static string $resource = PageResource::class;

    /** @return array<\Filament\Actions\Action> */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
