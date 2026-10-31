---
title: Creating bricks
description: Scaffold a brick class and its view, and configure its form, icon and tags.
---

# Creating bricks

A brick is a class with an associated Blade view that is rendered in the editor with its data. They follow the same conventions as Filament's RichEditor custom blocks.

## Scaffolding one

```bash
php artisan make:mason-brick Section
```

This writes the class into the namespace from `generator.namespace` (default `App\Mason`) and its Blade templates into `generator.views_path` under `resources/views` (default `mason`). Pass `--force` to overwrite an existing brick.

## Anatomy

```php
use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Section extends Brick
{
    public static function getId(): string
    {
        return 'section';
    }

    public static function getLabel(): string
    {
        return parent::getLabel();
    }

    public static function getIcon(): string | Heroicon | Htmlable | null
    {
        return Heroicon::OutlinedSquares2x2;
    }

    public static function getTags(): array
    {
        return ['section', 'content', 'layout'];
    }

    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason.section', [
            'background_color' => $config['background_color'] ?? 'white',
            'image' => $config['image'] ?? null,
            'text' => $config['text'] ?? null,
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->slideOver()
            ->schema([
                Radio::make('background_color'),
                FileUpload::make('image'),
                RichEditor::make('text'),
            ]);
    }
}
```

| Method | Purpose |
|---|---|
| `getId()` | **Required.** The identifier stored in the JSON. The only abstract method. |
| `getLabel()` | Sidebar label. Derived from the class name unless overridden. |
| `getIcon()` | Sidebar icon. A `Heroicon` case, an icon name, or an `Htmlable` for inline SVG. |
| `getTags()` | Search terms. Empty by default. |
| `toHtml()` | Renders the brick from its saved `$config`, plus any `$data` passed by the renderer. |
| `configureBrickAction()` | Builds the form shown when inserting or editing. |

The form does not close when the user clicks outside it, so unsaved changes are not lost by accident. To allow it for a brick, call `closeModalByClickingAway()` in `configureBrickAction()`.

`getId()` is what ties stored content to a class, so changing it on a brick that is already in use orphans the existing content.

A brick without settings, such as a divider, keeps the default `configureBrickAction()`, which hides the modal. It is inserted right away, and the editor shows no **Edit** control for it and ignores double-clicks on it. `isEditable()` works this out from the action; override it if your brick needs a different answer.

## Tags

Tags improve discoverability in the sidebar search. Mason matches the typed term against both the label and every tag, so a search for "marketing" can surface a brick labelled "Hero":

```php
public static function getTags(): array
{
    return ['hero', 'banner', 'header', 'landing page', 'marketing'];
}
```

## Outline labels

When a field has the [outline](../fields/customizing.md#outline) enabled, each brick is listed by its label, so a page with several Sections shows several identical rows. `getOutlineLabel()` gives each one its own name, worked out from its saved config. The outline shows it with the brick's label underneath:

```php
public static function getOutlineLabel(array $config): ?string
{
    return $config['heading'] ?? null;
}
```

Returning `null` or an empty string falls back to the brick's label. It is only called when the outline is enabled, and the result is never saved with the content. The built-in Section brick uses the first 60 characters of its text.

## Rendering from the record

`toHtml()` takes a second argument holding whatever context the renderer was given — most usefully the record the content belongs to. It is empty unless something passes it, so guard the keys you read:

```php
public static function toHtml(array $config, ?array $data = null): ?string
{
    $record = $data['record'] ?? null;

    return view('mason.byline', [
        'heading' => $config['heading'] ?? null,
        'author' => $record?->author?->name,
        'published' => $record?->published_at,
    ])->render();
}
```

See [Rendering](rendering.md#passing-data-to-bricks) for how to supply it.

> [!WARNING]
> The editor preview does not supply `$data`. It renders in an iframe that has no record in scope, so a record-dependent brick renders its fallback there. Write the fallback so the brick still reads sensibly while it is being edited.

## Parts

A brick with a long form can let authors jump straight to the field they mean. Mark elements of the brick's view as parts with `mason_part()`, named after the key of a component in the brick's form: a field name, or the `key()` of a section or tab:

```blade
<section {{ mason_part('design') }}>
    <h1 {{ mason_part('heading') }}>{{ $heading }}</h1>
</section>
```

```php
Tabs::make()->tabs([
    Tab::make('Content')->key('content')->schema([
        TextInput::make('heading'), // part "heading"
    ]),
    Tab::make('Design')->key('design')->schema([...]), // part "design"
])
```

For repeated content, name the path through the repeater or builder with the item's position, starting at `0`. `cards.1.heading` opens the heading of the second card, `cards.1` the card itself:

```blade
@foreach ($cards as $card)
    <article {{ mason_part("cards.{$loop->index}") }}>
        <h3 {{ mason_part("cards.{$loop->index}.heading") }}>{{ $card['heading'] }}</h3>
    </article>
@endforeach
```

In the preview, the first click on a brick still selects it. A click on a part of the selected brick, or a double-click on it with [double-click to edit](../fields/form.md#editing-on-double-click), opens the brick's form there: tabs switch, collapsed sections and repeater items open, and the component scrolls into view with its first input focused. Parts can be nested, and the innermost one wins. The whole form is still submitted, so validation and dependent fields work as before. A part whose key isn't in the form, or is hidden, opens the form as usual. The brick's content still takes no clicks in the preview, so links, embeds and videos inside a part stay inert. An `afterFormFilled()` the brick sets in `configureBrickAction()` runs first, and the form still opens at the part.

A bare key matches the first component with that key in the order of the form, which can be a field inside a repeater rather than a field of the same name further down. Give such components distinct keys, or name the path, e.g. `cards.0.title`.

`mason_part()` outputs `data-mason-part` only in the editor preview, so nothing reaches the front end or an entry. A brick that caches its rendered HTML should therefore not reuse the preview's HTML elsewhere, or the other way round: skip the cache while `Mason::isRenderingPreview()` is true, or make it part of the cache key.

## Rendering in the editor

`Mason::isRenderingPreview()` tells a brick that it is being rendered for the editor preview, e.g. to show a placeholder where the front end would show nothing:

```php
public static function toHtml(array $config, ?array $data = null): ?string
{
    if (blank($config['items'] ?? null) && ! Mason::isRenderingPreview()) {
        return null;
    }

    // ...
}
```

Unlike a `null` `$data`, it holds when the field passes [preview data](../fields/form.md#preview-data) too.

## Bricks without a form

For a brick that needs no configuration — a divider, a fixed callout — return the view from `toHtml()` and hide the modal, so inserting it adds it immediately:

```php
public static function toHtml(array $config, ?array $data = null): ?string
{
    return view('mason.static-brick');
}

public static function configureBrickAction(Action $action): Action
{
    return $action->modalHidden();
}
```
