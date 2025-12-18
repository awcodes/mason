<?php

declare(strict_types=1);

use Awcodes\Mason\Support\MasonRenderer;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Htmlable;

if (! function_exists(function: 'mason')) {
    function mason(string | array | stdClass | null $content, ?array $bricks = []): MasonRenderer
    {
        return MasonRenderer::make($content)->bricks($bricks);
    }
}

if (! function_exists(function: 'sanitize_livewire')) {
    function sanitize_livewire(string | Htmlable $html): string | Htmlable
    {
        return Str::of($html)
            ->pipe(fn ($html) => $html->replaceMatches('/wire:ignore/', ''))
            ->pipe(fn ($html) => $html->replaceMatches('/wire:key=".*?"/', ''))
            ->pipe(fn ($html) => $html->replaceMatches('/wire:id=".*?"/', ''))
            ->pipe(fn ($html) => $html->replaceMatches('/wire:effects=".*?"/', ''))
            ->pipe(fn ($html) => $html->replaceMatches('/wire:initial-data=".*?"/', ''))
            ->pipe(fn ($html) => $html->replaceMatches('/wire:snapshot=".*?"/', ''))
            ->pipe(fn ($html) => $html->replaceMatches('/wire:ignore.self/', ''))
            ->pipe(fn ($html) => $html->replaceMatches('/wire:submit=".*?"/', ''))
            ->pipe(fn ($html) => $html->replaceMatches('/wire:model=".*?"/', ''))
            ->pipe(fn ($html) => $html->replace("\n", ''))
            ->pipe(fn ($html) => $html->replace('type="submit"', ''))
            ->pipe(fn ($html) => $html->replace('<!--[if BLOCK]><![endif]-->', ''))
            ->pipe(fn ($html) => $html->replace('<!--[if ENDBLOCK]><![endif]-->', ''))
            ->pipe(fn ($html) => $html->replace('<form', '<div'))
            ->pipe(fn ($html) => $html->replace('form>', 'div>'))
            ->squish()
            ->toHtmlString();
    }
}
