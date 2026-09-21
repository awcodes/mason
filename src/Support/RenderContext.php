<?php

declare(strict_types=1);

namespace Awcodes\Mason\Support;

use Awcodes\Mason\Brick;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use JsonException;

/**
 * Carries a field's bricks and layout across the iframe request boundary.
 *
 * The preview and entry render in an iframe fed by a POST to the Mason
 * controller, so the controller cannot see the field that owns them. Both the
 * brick classes and the layout view name decide what code runs server side,
 * so they travel encrypted: a client can replay what it was given, but cannot
 * name its own classes or views.
 */
class RenderContext
{
    /**
     * @param  array<class-string<Brick>>  $bricks
     */
    public static function encode(array $bricks, ?string $layout): string
    {
        return Crypt::encryptString(json_encode([
            'bricks' => array_values($bricks),
            'layout' => $layout,
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * @return array{bricks: array<class-string<Brick>>, layout: ?string}
     */
    public static function decode(mixed $payload): array
    {
        $empty = ['bricks' => [], 'layout' => null];

        if (! is_string($payload) || $payload === '') {
            return $empty;
        }

        try {
            $decoded = json_decode(Crypt::decryptString($payload), true, flags: JSON_THROW_ON_ERROR);
        } catch (DecryptException | JsonException) {
            // A context we did not sign renders nothing rather than failing.
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
}
