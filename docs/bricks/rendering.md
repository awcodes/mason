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
> A brick whose class is missing from the list you pass renders as an "unregistered brick" placeholder rather than failing. If content comes out with gaps, check the brick list before the content.
