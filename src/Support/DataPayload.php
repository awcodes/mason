<?php

declare(strict_types=1);

namespace Awcodes\Mason\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;
use JsonException;
use JsonSerializable;

/**
 * Carries brick render data across the iframe request boundary.
 *
 * The entry renders in an iframe fed by a POST to the Mason controller, so
 * anything a brick needs beyond its own config has to travel with the request.
 * Eloquent models are reduced to a class/key marker and re-resolved on the far
 * side; the whole payload is encrypted so a client cannot swap in a record it
 * was never given.
 */
class DataPayload
{
    protected const MODEL_MARKER = '__mason_model';

    /**
     * @param  array<string, mixed>  $data
     */
    public static function encode(array $data): ?string
    {
        if ($data === []) {
            return null;
        }

        try {
            $json = json_encode(static::dehydrate($data), JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException('Mason entry data must be JSON-serializable, or an Eloquent model. [' . $e->getMessage() . ']', $e->getCode(), previous: $e);
        }

        return Crypt::encryptString($json);
    }

    /**
     * @return array<string, mixed>
     */
    public static function decode(mixed $payload): array
    {
        if (! is_string($payload) || $payload === '') {
            return [];
        }

        try {
            $decoded = json_decode(Crypt::decryptString($payload), true, flags: JSON_THROW_ON_ERROR);
        } catch (DecryptException | JsonException) {
            // A payload we did not sign is treated as absent rather than fatal:
            // the brick sees no data and renders its own fallback.
            return [];
        }

        return is_array($decoded) ? static::hydrate($decoded) : [];
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    protected static function dehydrate(array $data): array
    {
        return array_map(function (mixed $value): mixed {
            if ($value instanceof Model) {
                return [
                    self::MODEL_MARKER => $value::class,
                    'key' => $value->getKey(),
                ];
            }

            if (is_array($value)) {
                return static::dehydrate($value);
            }

            // json_encode() quietly turns a closure into {} rather than failing,
            // so unsupported values have to be rejected before they are encoded.
            if (is_object($value) && ! $value instanceof JsonSerializable && ! $value instanceof Arrayable) {
                throw new InvalidArgumentException(
                    'Mason render data cannot contain [' . $value::class . ']. Pass an Eloquent model, a scalar, or something JSON-serializable.',
                );
            }

            if (is_resource($value)) {
                throw new InvalidArgumentException('Mason render data cannot contain a resource.');
            }

            return $value;
        }, $data);
    }

    /**
     * @param  array<mixed>  $data
     * @return array<mixed>
     */
    protected static function hydrate(array $data): array
    {
        return array_map(function (mixed $value): mixed {
            if (! is_array($value)) {
                return $value;
            }

            $class = $value[self::MODEL_MARKER] ?? null;

            if (is_string($class) && is_subclass_of($class, Model::class)) {
                // The record may have been deleted between render and request.
                return $class::query()->find($value['key'] ?? null);
            }

            return static::hydrate($value);
        }, $data);
    }
}
