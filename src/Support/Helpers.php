<?php

namespace Awcodes\Mason\Support;

use Awcodes\Mason\Mason;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Throwable;

class Helpers
{
    public static function isAuthRoute(): bool
    {
        $authRoutes = [
            '/login',
            '/password-reset',
            '/register',
            '/email-verification',
        ];

        return Str::of(Request::path())->contains($authRoutes);
    }

    /**
     * @throws Throwable
     */
    public static function renderBricks(array $document, Mason $component): array
    {
        $content = $document['content'];

        foreach ($content as $k => $brick) {
            if ($brick['type'] === 'masonBrick') {
                $instance = $component->getAction($brick['attrs']['identifier']);
                if ($instance) {
                    $view = view($brick['attrs']['path'], $brick['attrs']['values'])->toHtml();
                    $content[$k]['attrs']['view'] = static::sanitizeLivewire($view);
                } else {
                    $content[$k]['attrs']['view'] = view('mason::components.unregistered-block', [
                        'identifier' => $brick['attrs']['identifier'],
                    ])->toHtml();
                }
            } elseif (array_key_exists('content', $brick)) {
                $content[$k] = self::renderBricks($brick, $component);
            }
        }

        $document['content'] = $content;

        return $document;
    }

    public static function sanitizeBricks(array $document): array
    {
        $content = $document['content'];

        foreach ($content as $k => $brick) {
            if ($brick['type'] === 'masonBrick') {
                unset($content[$k]['attrs']['view']);
            } elseif (array_key_exists('content', $brick)) {
                $content[$k] = self::sanitizeBricks($brick);
            }
        }

        $document['content'] = $content;

        return $document;
    }

    public static function sanitizeLivewire(string $html): string
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
            ->pipe(fn ($html) => $html->replace('<!--[if BLOCK]><![endif]-->', ''))
            ->pipe(fn ($html) => $html->replace('<!--[if ENDBLOCK]><![endif]-->', ''))
//            ->pipe(fn ($html) => $html->replace('form', 'div'))
            ->squish()
            ->toHtmlString();
    }

    public static function isHTML($text): bool
    {
        $processed = htmlentities($text);

        if ($processed == $text) {
            return false;
        }

        return true;
    }
}
