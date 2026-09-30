<?php

declare(strict_types=1);

use Illuminate\Support\Arr;

$langPath = __DIR__ . '/../../resources/lang';

it('translates every English key', function (string $locale) use ($langPath) {
    $english = array_keys(Arr::dot(require "{$langPath}/en/mason.php"));
    $translated = array_keys(Arr::dot(require "{$langPath}/{$locale}/mason.php"));

    expect(array_values(array_diff($english, $translated)))->toBe([])
        ->and(array_values(array_diff($translated, $english)))->toBe([]);
})->with(fn () => collect(glob("{$langPath}/*", GLOB_ONLYDIR))
    ->map(fn (string $path): string => basename($path))
    ->reject('en')
    ->values()
    ->all());
