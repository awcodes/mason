---
title: Installation
description: Install Mason and import its stylesheet into your Filament theme.
---

# Installation

## Compatibility

| Filament version | Package version |
|------------------|-----------------|
| 3.x              | 0.x             |
| 4.x              | 1.x             |
| 5.x              | 2.x             |
| 4.x & 5.x        | 3.x             |

Mason requires PHP 8.2 or later and `filament/filament`.

## Requiring the package

```bash
composer require awcodes/mason
```

## Registering the styles

Mason requires a custom theme — it aligns with Filament's theming approach rather than shipping its own compiled CSS.

> [!IMPORTANT]
> If you have not set up a custom theme and are using Filament Panels, follow the instructions in the [Filament documentation](https://filamentphp.com/docs/5.x/styling/overview#creating-a-custom-theme) first. This applies to both the Panels package and the standalone Forms package.

Add Mason's CSS and views to your theme's CSS file — or your application's CSS file if you are using the standalone forms package:

```css
@import '../../../../vendor/awcodes/mason/resources/css/plugin.css';

@source '../../../../vendor/awcodes/mason/resources/**/*.blade.php';
```

## Preparing your model

Mason stores its content as JSON. Cast the attribute on your model:

```php
protected $casts = [
    'content' => 'array', // or 'json'
];
```

A `longText` column is recommended, since a document with several bricks grows quickly.

Next, add the field to a form — see [Form field](fields/form.md).

## Configuring Livewire's maximum nesting depth

Mason synchronizes its document with Livewire as nested data. Livewire limits nested property paths to 10 levels by default, and a Mason document reaches that quickly: each brick's config sits inside the document, and a brick whose form holds repeaters or other structured fields nests further still. If you encounter a `Livewire\Exceptions\MaxNestingDepthExceededException` and your application does not already have a `config/livewire.php` file, publish Livewire's configuration file:

```bash
php artisan livewire:publish --config
```

The command overwrites an existing `config/livewire.php` file, so skip it if you have already published the configuration.

Then, increase the existing `max_nesting_depth` setting in `config/livewire.php`. For example, a depth of 32 leaves room for bricks with nested fields:

```php
'payload' => [
    // ...
    'max_nesting_depth' => 32,
],
```

Only change the `max_nesting_depth` value in the existing `payload` array, so that you preserve Livewire's other version-specific payload settings.

## Upgrading content stored by Mason 0.x

Bricks written by Mason 0.x use a different attribute shape — `identifier`, `values` and `path` — from the `id` and `config` the renderer reads today. Content saved before 1.0 renders as nothing until it is converted.

`mason:upgrade-bricks` rewrites it in place:

```bash
php artisan mason:upgrade-bricks --table=posts --column=content
```

It prompts for the table and column if you omit the options, and walks the column recursively so nested bricks are converted too.

> [!WARNING]
> This overwrites the column. Back up the table first.

This is a one-time migration off the 0.x schema. If you have never stored content with Mason 0.x, you do not need it. See the [upgrade guide](https://github.com/awcodes/mason/blob/3.x/UPGRADE.md) for the rest of that migration.
