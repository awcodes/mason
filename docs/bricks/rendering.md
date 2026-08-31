---
title: Rendering
description: Turn stored Mason content into HTML on the front end.
---

# Rendering

Content is stored as JSON, so you are free to render it however you like. Mason also ships a renderer, which needs the same brick list the field used — this is where a [reusable collection](organizing.md) pays off.

## The helper

```blade
{!! mason(content: $post->content, bricks: \App\Mason\BrickCollection::make())->toHtml() !!}
```

## The Blade directive

`@mason` is shorthand for the helper followed by `toHtml()`, and takes the same two arguments:

```blade
@mason($post->content, \App\Mason\BrickCollection::make())
```

Pass the brick list. Given content alone, `@mason` renders with the default list — `Section` and nothing else — so your own bricks produce no output.

## The renderer

For more control, use `MasonRenderer` directly:

```php
use Awcodes\Mason\Support\MasonRenderer;

$renderer = MasonRenderer::make($post->content)
    ->bricks(\App\Mason\BrickCollection::make());
```

| Method | Returns |
|---|---|
| `toHtml()` | Rendered, escaped HTML. |
| `toUnsafeHtml()` | Rendered HTML without escaping. |
| `toArray()` | The decoded structure. |
| `toText()` | Plain text, for excerpts and search indexing. |

> [!WARNING]
> A brick whose class is missing from the list you pass renders as nothing at all — no placeholder, no error. If content comes out with gaps, check the brick list before you go looking at the content.
