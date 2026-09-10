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

## Passing data to bricks

Bricks receive a second argument alongside their config, so content can render against the record it belongs to. Pass it as the third argument to the helper or the directive:

```blade
{!! mason($post->content, \App\Mason\BrickCollection::make(), ['record' => $post])->toHtml() !!}
```

```blade
@mason($post->content, \App\Mason\BrickCollection::make(), ['record' => $post])
```

The array is arbitrary — `record` is only a convention. Anything you put there reaches every brick's `toHtml()`:

```php
public static function toHtml(array $config, ?array $data = null): ?string
{
    $record = $data['record'] ?? null;

    return view('mason.byline', ['author' => $record?->author?->name])->render();
}
```

## The renderer

For more control, use `MasonRenderer` directly:

```php
use Awcodes\Mason\Support\MasonRenderer;

$renderer = MasonRenderer::make($post->content)
    ->bricks(\App\Mason\BrickCollection::make())
    ->data(['record' => $post]);
```

`data()` also accepts a closure, evaluated when the content renders.

| Method | Returns |
|---|---|
| `toHtml()` | Rendered, escaped HTML. |
| `toUnsafeHtml()` | Rendered HTML without escaping. |
| `toArray()` | The decoded structure. |
| `toText()` | Plain text, for excerpts and search indexing. |

> [!WARNING]
> A brick whose class is missing from the list you pass renders as nothing at all — no placeholder, no error. If content comes out with gaps, check the brick list before you go looking at the content.

## In an infolist

`MasonEntry` passes its own record automatically, so a brick that reads `$data['record']` works with no extra wiring:

```php
MasonEntry::make('content')
    ->bricks(\App\Mason\BrickCollection::make());
```

Override it with `data()` when a brick needs something else:

```php
MasonEntry::make('content')
    ->bricks(\App\Mason\BrickCollection::make())
    ->data(fn ($record): array => ['record' => $record, 'locale' => app()->getLocale()]);
```

The entry renders inside an iframe fed by a separate request, so its data crosses a request boundary. Eloquent models travel as an encrypted class-and-key reference and are re-resolved on arrival — the model's attributes never go over the wire, and a tampered payload is discarded rather than trusted. Everything else you put in `data()` must be JSON-serializable; a closure or arbitrary object throws rather than silently vanishing.

> [!NOTE]
> A record deleted between rendering the page and the iframe's request arrives as `null`, so keep reading it defensively in the brick.
