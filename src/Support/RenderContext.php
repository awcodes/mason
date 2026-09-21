<?php

declare(strict_types=1);

namespace Awcodes\Mason\Support;

use Awcodes\Mason\Brick;
use Illuminate\Support\Facades\Crypt;
use JsonException;

/**
 * Carries a field's bricks and layout across the iframe request boundary.
 *
 * The preview and entry render in an iframe fed by a POST to the Mason
 * controller, so the controller cannot see the field that owns them. Both the
 * brick classes and the layout view name decide what code runs server side,
 * so they travel signed: a client can replay what it was given, but cannot
 * name its own classes or views.
 *
 * The token is signed rather than encrypted because nothing in it is secret,
 * and it must be deterministic: it lives in the component's x-data, and a
 * value that changed on every Livewire render would break the editor preview.
 */
class RenderContext
{
    /**
     * @param  array<class-string<Brick>>  $bricks
     */
    public static function encode(array $bricks, ?string $layout): string
    {
        $json = json_encode([
            'bricks' => array_values($bricks),
            'layout' => $layout,
        ], JSON_THROW_ON_ERROR);

        return base64_encode($json) . '.' . static::sign($json);
    }

    /**
     * @return array{bricks: array<class-string<Brick>>, layout: ?string}
     */
    public static function decode(mixed $payload): array
    {
        $empty = ['bricks' => [], 'layout' => null];

        if (! is_string($payload) || ! str_contains($payload, '.')) {
            return $empty;
        }

        [$encoded, $signature] = explode('.', $payload, 2);
        $json = base64_decode($encoded, strict: true);

        // A context we did not sign renders nothing rather than failing.
        if ($json === false || ! hash_equals(static::sign($json), $signature)) {
            return $empty;
        }

        try {
            $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $empty;
        }

        if (! is_array($decoded)) {
            return $empty;
        }

        $bricks = is_array($decoded['bricks'] ?? null) ? $decoded['bricks'] : [];
        $layout = $decoded['layout'] ?? null;

        return [
            // Signed by us, but a brick class may have been renamed or removed
            // since the page rendered.
            'bricks' => array_values(array_filter(
                $bricks,
                fn (mixed $brick): bool => is_string($brick) && is_subclass_of($brick, Brick::class),
            )),
            'layout' => is_string($layout) && $layout !== '' ? $layout : null,
        ];
    }

    protected static function sign(string $json): string
    {
        // The encrypter throws on a missing app key rather than signing with an
        // empty one. The prefix scopes the signature to Mason.
        return hash_hmac('sha256', 'mason.render-context|' . $json, Crypt::getKey());
    }
}
