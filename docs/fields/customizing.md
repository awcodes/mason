---
title: Customizing
description: Adjust the Mason editor's height, sidebar position, action layout and colour mode.
---

# Customizing

## Height

Both the field and the entry take Filament's `extraInputAttributes()`, which is the way to change the editor's height:

```php
Mason::make('content')
    ->extraInputAttributes(['style' => 'min-height: 30rem;'])
    ->bricks([...]);

MasonEntry::make('content')
    ->extraInputAttributes(['style' => 'min-height: 40rem;'])
    ->bricks([...]);
```

## Sidebar position

The brick sidebar sits on the right. Move it to the left with `SidebarPosition::Start`:

```php
use Awcodes\Mason\Enums\SidebarPosition;

Mason::make('content')
    ->sidebarPosition(SidebarPosition::Start)
    ->bricks([...]);
```

The enum has two cases, `Start` and `End`, so they follow writing direction rather than being fixed to left and right.

## Brick actions as a grid

Sidebar bricks are listed vertically by default. To lay them out as a grid instead:

```php
Mason::make('content')
    ->displayActionsAsGrid()
    ->bricks([...]);
```

## Light and dark mode

Add a toggle to the editor sidebar so authors can preview both modes:

```php
Mason::make('content')
    ->colorModeToggle()
    ->defaultColorMode('dark')
    ->bricks([...]);
```

`defaultColorMode()` sets only the initial mode. Once an author switches, their choice is kept in local storage for later visits.

For this to work your application's CSS must support manually toggling dark mode, per Tailwind's [manual dark mode](https://tailwindcss.com/docs/dark-mode#toggling-dark-mode-manually) documentation:

```css
@custom-variant dark (&:where(.dark, .dark *));
```

> [!NOTE]
> `sidebarPosition()`, `displayActionsAsGrid()` and the colour mode methods are editor-only. `MasonEntry` has no sidebar, though it does accept `extraInputAttributes()`.
