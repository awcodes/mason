# Upgrade Guide

This guide will help you upgrade from Mason 0.x to 1.x.

## High Impact Changes

- [Filament v4 Upgrade](#filament-v4-upgrade)
- [Brick Architecture Redesign](#brick-architecture-redesign)
- [Mason Component API Changes](#mason-component-api-changes)
- [Database Schema](#database-schema)

## Medium Impact Changes

- [Dependency Updates](#dependency-updates)
- [Support Helpers and Converter Removal](#support-helpers-and-converter-removal)
- [Helper Function Changes](#helper-function-changes)
- [Translation Changes](#translation-changes)
- [Livewire Renderer Removal](#livewire-renderer-removal)

---

## Filament v4 Upgrade

Mason 1.x now requires Filament v4. Please follow the [Filament v4 Upgrade Guide](https://filamentphp.com/docs/4.x/upgrade-guide) before upgrading Mason.

## Brick Architecture Redesign

The `Brick` architecture has been completely redesigned to be more robust and easier to manage. In 0.x, Bricks were created using a fluent API that extended Filament's action class. In 1.x, they are now dedicated classes that extend `Awcodes\Mason\Brick`.

### Action required

You must update all your custom Bricks to the new class-based structure.

**Before (0.x):**

```php
use Awcodes\Mason\Brick;
use Awcodes\Mason\EditorCommand;
use Awcodes\Mason\Mason;

Brick::make('section')
    ->label('Section')
    ->form([
        // ...
    ])
    ->action(function (array $arguments, array $data, Mason $component) {
        $component->runCommands(
            [
                new EditorCommand(
                    name: 'setBrick',
                    arguments: [[
                        'identifier' => 'section',
                        'values' => $data,
                        'path' => 'mason::bricks.section',
                        'view' => view('mason::bricks.section', $data)->toHtml(),
                    ]],
                ),
            ],
            editorSelection: $arguments['editorSelection'],
        );
    });
```

**After (1.x):**

```php
use Awcodes\Mason\Brick;
use Filament\Actions\Action;

class Section extends Brick
{
    public static function getId(): string
    {
        return 'section';
    }

    public static function toHtml(array $config, array $data): ?string
    {
        return view('mason::bricks.section.index', $config)->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->slideOver()
            ->schema([
                // ...
            ]);
    }
}
```

## Mason Component API Changes

The `bricks()` method now expects an array of class strings instead of an array of `Brick` instances.

### Action required

Update your `Mason` component definitions:

**Before (0.x):**

```php
Mason::make('content')
    ->bricks([
        Section::make(),
    ])
```

**After (1.x):**

```php
Mason::make('content')
    ->bricks([
        Section::class,
    ])
```

## Database Schema

Blocks stored in the database need to be updated to the new brick schema.

**Before (0.x)**

```json
{
    "type":"masonBrick",
    "attrs":{
        "identifier": "newsletter_signup",
        "path": "mason.newsletter-signup",
        "values": {
            "background_color":"primary",
            "heading":"Want product news and updates? Sign up for our newsletter."
        }
    }
}
```

**After (1.x)**

```json
{
    "type": "masonBrick",
    "attrs": {
        "id": "newsletter_signup",
        "config": {
            "background_color": "primary",
            "heading": "Want product news and updates? Sign up for our newsletter."
        }
    }
}
```

### Upgrade Command

To help with this migration, a new command has been added to the package:

```bash
php artisan mason:upgrade-bricks
```

This command will prompt you for the table and column you wish to update and will recursively find and update all Mason bricks in that column.


## Dependency Updates

- PHP 8.2+ is now required.
- `ueberdosis/tiptap-php` has been upgraded to v2.
- `filament/filament` has been upgraded to v4.

## Support Helpers and Converter Removal

The `Awcodes\Mason\Support\Helpers` and `Awcodes\Mason\Support\Converter` classes have been removed.

If you were using `Helpers::sanitizeBricks()`, this is now handled internally by the `Mason` component using Tiptap PHP during hydration and dehydration.

## Helper Function Changes

The `mason()` helper function now returns an instance of `Awcodes\Mason\Support\MasonRenderer` instead of the removed `Converter` class.

## Translation Changes

The translation keys have been restructured. If you have published and customized the translation files, you will need to update them to match the new structure.

**Before:**
`mason.insert_brick`

**After:**
`mason.actions.brick.modal.actions.insert.label`

## Livewire Renderer Removal

The `mason.renderer` Livewire component and its associated render hook have been removed as they are no longer needed for the new architecture. If you were manually referencing this component, you should remove those references.

