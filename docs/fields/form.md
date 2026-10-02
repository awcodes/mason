---
title: Form field
description: Add the Mason editor to a Filament form and point it at a preview layout.
---

# Form field

```php
use Awcodes\Mason\Bricks\Section;
use Awcodes\Mason\Mason;

Mason::make('content')
    ->bricks([
        Section::class,
    ]);
```

The name is the attribute on your model — cast to `array` or `json`, as covered in [Installation](../installation.md).

`bricks()` lists what the author can insert. It also accepts a closure, and if you omit it entirely Mason falls back to its built-in `Section` brick, which is useful for a first look but rarely what you want in an application. See [Creating bricks](../bricks/creating.md).

## Working in the editor

Clicking a brick in the preview selects it and shows its controls: move up, move down, add, edit, and delete.

![A selected hero brick with its controls in the top corner](../assets/block-controls-light.png#gh-light-mode-only)
![A selected hero brick with its controls in the top corner](../assets/block-controls-dark.png#gh-dark-mode-only)

**Add** opens the brick picker, where the author chooses whether to insert above or below the selected brick, and can search the available bricks.

![The Add Brick picker with Insert above and Insert below, a search field, and the Content group of bricks](../assets/brick-picker-light.png#gh-light-mode-only)
![The Add Brick picker with Insert above and Insert below, a search field, and the Content group of bricks](../assets/brick-picker-dark.png#gh-dark-mode-only)

**Edit** opens the brick's form. How it opens is up to the brick's `configureBrickAction()`; the hero brick here uses a slide-over.

![The hero brick's form open in a slide-over](../assets/edit-brick-light.png#gh-light-mode-only)
![The hero brick's form open in a slide-over](../assets/edit-brick-dark.png#gh-dark-mode-only)

The sidebar toolbar switches the preview between mobile and tablet widths and toggles fullscreen, so authors can check responsive layouts while they work. It also has clear all, undo, and redo.

![The editor previewing the page at mobile width](../assets/mobile-preview-light.png#gh-light-mode-only)
![The editor previewing the page at mobile width](../assets/mobile-preview-dark.png#gh-dark-mode-only)

## Preview layout

The editor renders inside an iframe, so it needs a layout carrying your application's styles. Without one the editor shows unstyled content that looks nothing like the front end.

Set a default for every field in the config, or per field:

```php
Mason::make('content')
    ->previewLayout('layouts.mason-preview')
    ->bricks([...]);
```

The layout is an ordinary Blade view. Include your own CSS, then Mason's preview styles and the content partial:

```blade
{{-- resources/views/layouts/mason-preview.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @masonStyles
    </head>
    <body>
        <main>
            @include('mason::iframe-preview-content', ['blocks' => $blocks])
        </main>
    </body>
</html>
```

`@masonStyles` inlines the editor chrome — drop zones, controls and outlines — and `mason::iframe-preview-content` renders the bricks themselves from the `$blocks` variable Mason passes in.

## Editor colours

The editor's overlay colours are CSS custom properties, so they can be restyled from your own stylesheet if the default blue clashes:

```css
#mason-preview-container {
    --mason-border-color: rgb(236, 72, 153);
    --mason-controls-background: rgba(0, 0, 0, 0.8);
    --mason-button-hover-background: rgba(255, 255, 255, 0.2);
    --mason-drop-zone-background: rgba(236, 72, 153, 0.5);
}
```

## Editing on double click

Bricks are edited through their edit button by default. To open the edit modal on double click as well:

```php
Mason::make('content')
    ->doubleClickToEdit()
    ->bricks([...]);
```

More field options are covered in [Customizing](customizing.md).
