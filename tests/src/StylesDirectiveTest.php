<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('reads the styles when the view renders, so that cached views get updated styles', function (string $directive, string $file) {
    $compiled = Blade::compileString("@{$directive}");

    expect($compiled)->toContain('file_get_contents(')
        ->not->toContain(substr((string) file_get_contents(__DIR__ . '/../../resources/css/' . $file), 0, 40))
        ->and(Blade::render("@{$directive}"))->toBe('<style>' . file_get_contents(__DIR__ . '/../../resources/css/' . $file) . '</style>');
})->with([
    'preview' => ['masonStyles', 'preview.css'],
    'entry' => ['masonEntryStyles', 'entry.css'],
]);
