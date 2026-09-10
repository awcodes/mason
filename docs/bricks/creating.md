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

`getId()` is what ties stored content to a class, so changing it on a brick that is already in use orphans the existing content.

## Tags

Tags improve discoverability in the sidebar search. Mason matches the typed term against both the label and every tag, so a search for "marketing" can surface a brick labelled "Hero":

```php
public static function getTags(): array
{
    return ['hero', 'banner', 'header', 'landing page', 'marketing'];
}
```

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
