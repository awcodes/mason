---
title: Installation
description: Install Mason and import its stylesheet into your Filament theme.
---

# Installation

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
