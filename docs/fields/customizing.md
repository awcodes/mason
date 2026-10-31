---
title: Customizing
description: Adjust the Mason editor's height, sidebar position, action layout, allowed commands and colour mode.
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

## Adding, moving and deleting

Authors can add, move and delete bricks by default. As on Filament's repeater, each can be turned off, e.g. for a page whose structure is fixed and only its content may change:

```php
Mason::make('content')
    ->addable(false)
    ->reorderable(false)
    ->deletable(false)
    ->bricks([...]);
```

All three take a closure as well, e.g. to decide by the user's role. Editing bricks always stays possible.

- Without `addable()`, the sidebar offers no bricks, and the add buttons and the brick picker are gone. With the [outline](#outline) enabled, the sidebar shows it without tabs.
- Without `reorderable()`, bricks can't be dragged in the preview or the outline, and their move controls and Alt + Up or Down do nothing.
- Without `deletable()`, the delete controls and **Clear all** are gone.

The server refuses turned-off commands too, both in `executeCommands()` and when the brick form is opened to insert. Moving and deleting happen in the browser, though, and its state reaches the server as a whole, so validate the saved content yourself where the structure must not change.

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
> `sidebarPosition()`, `displayActionsAsGrid()`, `outline()` and the colour mode methods are editor-only. `MasonEntry` has no sidebar, though it does accept `extraInputAttributes()`.
