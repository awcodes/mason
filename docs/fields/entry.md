---
title: Infolist entry
description: Render stored Mason content read-only in a Filament infolist.
---

# Infolist entry

`MasonEntry` displays stored content in an infolist. It takes the same `bricks()` list as the field, so it knows how to render what it finds.

```php
use Awcodes\Mason\Bricks\Section;
use Awcodes\Mason\MasonEntry;

MasonEntry::make('content')
    ->bricks([
        Section::class,
    ]);
```

## Preview layout

Like the field, the entry renders inside an iframe and needs a layout carrying your styles. Set a default in the config under `entry.layout`, or per entry:

```php
MasonEntry::make('content')
    ->previewLayout('layouts.mason-entry')
    ->bricks([...]);
```

The layout differs from the field's in two places — it uses `@masonEntryStyles` and the entry content partial, because an entry has no editor chrome to style:

```blade
{{-- resources/views/layouts/mason-entry.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @masonEntryStyles
    </head>
    <body>
        <main>
            @include('mason::iframe-entry-content', ['blocks' => $blocks])
        </main>
    </body>
</html>
```

> [!NOTE]
> The sidebar options in [Customizing](customizing.md) — position, grid actions — apply only to the editor field. An entry has no sidebar.
