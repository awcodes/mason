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

![The brick sidebar: the toolbar, a brick search, and the Content group of bricks](../assets/brick-sidebar-light.png#gh-light-mode-only)
![The brick sidebar: the toolbar, a brick search, and the Content group of bricks](../assets/brick-sidebar-dark.png#gh-dark-mode-only)

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

## Outline

For long documents, add an outline of the content to the sidebar. It lists every brick in order. Drag a brick's handle to move it, or focus a brick and press <kbd>Alt</kbd> + <kbd>↑</kbd> or <kbd>↓</kbd>. Selecting a brick in the outline scrolls the preview to it.

```php
Mason::make('content')
    ->outline()
    ->bricks([...]);
```

The outline sits in an **Outline** tab next to the brick list. On wide screens in fullscreen mode, it moves into its own column on the other side of the preview, so both are visible at once. On small screens, where the sidebar is hidden, an outline button in the editor toolbar opens it as a panel from the bottom of the screen.

To tell apart several bricks of the same type, give them [outline labels](../bricks/creating.md#outline-labels).

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

## Showing the layout

A [preview layout](form.md#preview-layout) can wrap the bricks in more than the content area, such as the site's header and footer, an email frame or the rest of a product page. Authors mostly want to see the bricks alone, so Mason can hide those parts and add a toggle to the editor toolbar that shows them:

```php
Mason::make('content')
    ->previewLayout('layouts.mason-preview')
    ->layoutToggle()
    ->bricks([...]);
```

Mark the parts in the preview layout with `data-mason-layout`. They stay hidden until an author switches the toggle on, and their choice is kept in local storage for later visits:

```blade
<body>
    <div data-mason-layout>
        @include('partials.header')
    </div>

    <main>
        @include('mason::iframe-preview-content', ['blocks' => $blocks])
    </main>

    <div data-mason-layout>
        @include('partials.footer')
    </div>
</body>
```

The toggle is labelled "Show layout". Name what it shows with `layoutToggleLabel()`:

```php
Mason::make('content')
    ->layoutToggle()
    ->layoutToggleLabel('Show header and footer')
```

> [!NOTE]
> `sidebarPosition()`, `displayActionsAsGrid()`, `outline()`, the colour mode methods and `layoutToggle()` are editor-only. `MasonEntry` has no sidebar, though it does accept `extraInputAttributes()`.
