---
title: Mason
description: A block-based drag and drop page builder field for Filament forms and infolists.
---

# Mason

Mason is a drag and drop page builder for Filament. Authors assemble a document from **bricks** — your own classes, each with a form for its settings and a Blade view for its output — and the result is stored as JSON.

Because the editor renders inside an iframe using your application's own stylesheet, what an author sees while building is what the front end will render.

## The pieces

- **A form field** — `Mason` gives you the editor, with a sidebar of available bricks.
- **An infolist entry** — `MasonEntry` renders stored content read-only.
- **Bricks** — classes extending `Brick`, scaffolded with `make:mason-brick`.
- **A renderer** — `MasonRenderer`, or the `mason()` helper, turns stored JSON into HTML on the front end.

## Compatibility

| Package version | Filament version |
|-----------------|------------------|
| 0.x             | 3.x              |
| 1.x             | 4.x              |
| 2.x             | 5.x              |
| 3.x             | 4.x & 5.x        |

Mason requires PHP 8.2 or later and `filament/filament`.

> [!IMPORTANT]
> Mason stores its data as JSON, so cast the attribute to `array` or `json` on your model, and use a `longText` column.

## Where to go next

- [Installation](installation.md) — install the package and import its CSS.
- [Form field](fields/form.md) — add the editor to a form and give it a preview layout.
- [Creating bricks](bricks/creating.md) — scaffold and write your own.
- [Rendering](bricks/rendering.md) — output stored content on the front end.
