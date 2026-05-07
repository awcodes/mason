# Mason

A simple block-based drag and drop page / document builder field for Filament.

[![Latest Version](https://img.shields.io/github/release/awcodes/mason.svg?style=flat-square&color=blue&label=Release)](https://github.com/awcodes/mason/releases)
[![MIT Licensed](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/awcodes/mason.svg?style=flat-square&color=blue&label=Downloads)](https://packagist.org/packages/awcodes/mason)
[![GitHub Repo stars](https://img.shields.io/github/stars/awcodes/mason?style=flat-square&color=blue&label=Stars)](https://github.com/awcodes/mason/stargazers)

## Compatibility

| Package Version | Filament Version |
|-----------------|------------------|
| 0.x             | 3.x              |
| 1.x             | 4.x              |
| 2.x             | 5.x              |
| 3.x             | 4.x, 5.x         |

<!-- [docs_start] -->

## Installation

You can install the package via composer:

```bash
composer require awcodes/mason
```

In an effort to align with Filament's theming methodology, you will need to use a custom theme to use this plugin.

> [!IMPORTANT]
> If you have not set up a custom theme and are using Filament Panels, follow the instructions in the [Filament Docs](https://filamentphp.com/docs/5.x/styling/overview#creating-a-custom-theme) first. The following applies to both the Panels Package and the standalone Forms package.

After setting up a custom theme, add the plugin's CSS to your theme CSS file or your app's CSS file if using the standalone forms package.

```css
@import '../../../../vendor/awcodes/mason/resources/css/plugin.css';

@source '../../../../vendor/awcodes/mason/resources/**/*.blade.php';
```

## Configuration

You can publish the config file with:

```bash
php artisan vendor:publish --tag="mason-config"
```

These are the contents of the published config file:

```php
return [
    'generator' => [
        'namespace' => 'App\\Mason',
        'views_path' => 'mason',
    ],
    'preview' => [
        'layout' => 'mason::iframe-preview',
    ],
    'entry' => [
        'layout' => 'mason::iframe-entry',
    ],
    'routes' => [
        'middleware' => ['web', 'auth'],
    ],
];
```

### Auth Guards

By default, Mason uses the `web` and `auth` middleware for its routes internally. If you are using a different guard or have multiple guards, you can customize the middleware used by updating the `routes.middleware` configuration option in the published config file.

```php
'routes' => [
    'middleware' => ['web', 'auth:admin'],
],
```

## Usage

> [!IMPORTANT]
> Mason uses JSON to store its data in the database, so it is important that you cast the field to either 'array' or 'json' on your model, and it's recommended to store the content as a `longText` column in the database.

### Form Field

In your Filament forms you should use the `Mason` component. The `Mason` component accepts a `name` prop which should be the name of the field in your model, and requires an array 'bricks' available to the editor.

```php
use Awcodes\Mason\Mason;
use Awcodes\Mason\Bricks\Section;

->schema([
    Mason::make('content')
        ->bricks([
            Section::class,
        ]),
])
```

#### Field Preview Layout

Since Mason uses an iframe to render in the editor, you should set the preview layout for the field to a view in your application that includes your app's styles. This will ensure that the content in the editor looks similar to how it will look on the front end of your site. If all Mason fields in your forms use the same layout, you can set a default in the config file. Otherwise, you can set it per field like so:

```php
Mason::make('content')
    ->previewLayout('layouts.mason-preview') // your app's layout
    ->bricks([...])
```

Then in your layout file you can include the necessary styles and includes to render the content correctly.

```blade
// resources/views/layouts/mason-preview.blade.php

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Mason Styles -->
        @masonStyles 
    </head>
    <body>
        <main>
            <!-- Include Mason content rendering -->
            @include('mason::iframe-preview-content', ['blocks' => $blocks])
        </main>
    </body>
</html>
```

If the blue color used in the editor doesn't work with your design, you can customize it with CSS in your app's CSS file.

```css
#mason-preview-container {
    --mason-border-color: rgb(236, 72, 153);
    --mason-controls-background: rgba(0, 0, 0, 0.8);
    --mason-button-hover-background: rgba(255, 255, 255, 0.2);
    --mason-drop-zone-background: rgba(236, 72, 153, 0.5);
}
```

#### Double-Clicking Bricks to Edit

By default, Mason requires you to click the edit button on each brick to edit its content. If you would like to enable double-clicking on bricks to open the edit modal, you can chain the `doubleClickToEdit` method on the field.

```php
Mason::make('content')
    ->doubleClickToEdit()
    ->bricks([...])
```

### Infolist Entry

In your Filament infolists you should use the `MasonEntry` component. The `MasonEntry` component accepts a `name` prop which should be the name of the field in your model, and requires an array of 'bricks' available to the entry.

```php
use Awcodes\Mason\MasonEntry;
use Awcodes\Mason\Bricks\Section;

->schema([
    MasonEntry::make('content')
        ->bricks([
            Section::class,
        ]),
])
```

#### Entry Preview Layout

Since Mason uses an iframe to render in the infolist, you should set the preview layout for the field to a view in your application that includes your app's styles. This will ensure that the content in the editor looks similar to how it will look on the front end of your site. If all Mason fields in your forms use the same layout, you can set a default in the config file. Otherwise, you can set it per field like so:

```php
MasonEntry::make('content')
    ->previewLayout('layouts.mason-entry')
    ->bricks([...])
```

Then in your layout file you can include the necessary styles and includes to render the content correctly.

```blade
// resources/views/layouts/mason-entry.blade.php

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Mason Entry Styles -->
        @masonEntryStyles
    </head>
    <body>
        <main>
            <!-- Include MasonEntry content rendering -->
            @include('mason::iframe-entry-content', ['blocks' => $blocks])
        </main>
    </body>
</html>
```

## Tips & Tricks

### Custom Height

If you find that the default height of the Mason editor or entry is not enough for your use case, you can customize using Filament's default `->extraInputAttrbutes()` method on both the `Mason` field and the `MasonEntry` component.

```php
Mason::make('content')
    ->extraInputAttributes(['style' => 'min-height: 30rem;'])
    ->bricks([...])

MasonEntry::make('content')
    ->extraInputAttributes(['style' => 'min-height: 40rem;'])
    ->bricks([...])
```

### Brick Collections

To keep from having to repeat yourself when assigning bricks to the editor and the entry, it would help to create sets of bricks that make sense for their use case. Then you can use that in the `bricks` method.

```php
class BrickCollection
{
    public static function make(): array
    {
        return [
            NewsletterSignup::class,
            Section::class,
            Cards::class,
            SupportCenter::class,
        ];
    }
}

Mason::make('content')
    ->bricks(BrickCollection::make())

MasonEntry::make('content')
    ->bricks(BrickCollection::make())
```

### Sidebar Position

By default, the Mason editor sidebar is positioned on the right side of the editor. If you would like to position it on the left side, you can chain the `sidebarPosition` method on the field and assign it the new position.

```php
use Awcodes\Mason\Enums\SidebarPosition;

Mason::make('content')
    ->sidebarPosition(SidebarPosition::Start)
    ->bricks([...])
```

### Displaying Brick Actions as a Grid

By default, the Mason editor displays the brick actions in a list. If you would like to display them in a grid format, you can chain the `displayActionsAsGrid` method on the field.

```php
Mason::make('content')
    ->displayActionsAsGrid()
    ->bricks([...])
```

### Sorting Bricks

By default, bricks are sorted in the order they are defined in the `bricks` array. If you would like to allow users to sort the bricks in the editor, you can chain the `sortBricks` method on the field.

```php
// Sort ascending (A-Z) by label
Mason::make('content')
    ->sortBricks('asc')
    ->bricks([...])

// Or simply (defaults to 'asc')
Mason::make('content')
    ->sortBricks()
    ->bricks([...])

// Sort descending (Z-A) by label
Mason::make('content')
    ->sortBricks('desc')
    ->bricks([...])
```

### Static Bricks without data or forms

If you would like to create a brick that does not require any data or forms, you can return the view in the `toHtml` method and set the action to have a hidden modal in the `configureBrickAction` method in your brick class. Now, when inserting the brick, it will simply add the brick without any configuration.

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

### Light / Dark Mode

If your application supports light and dark mode, you can optionally add support for it in the Mason editor by using the `colorModeToggle` method on the field. This will add a toggle button to the editor sidebar that allows users to switch between light and dark mode. You can also use the `defaultColorMode` method to set the default color mode for the editor. One thing to note is that `defaultColorMode` only sets the initial mode when the editor is loaded. If the user switches modes, their preference will be saved in local storage for future visits.

In order for this to work properly, you will need to ensure that your application's CSS supports manually setting light and dark mode according to the Tailwind CSS documentation on [manually controlling color mode](https://tailwindcss.com/docs/dark-mode#toggling-dark-mode-manually).

```css
@custom-variant dark (&:where(.dark, .dark *));
```

```php
Mason::make('content')
    ->colorModeToggle()
    ->defaultColorMode('dark')
    ->bricks([...])
``` 
     
## Creating Bricks

Bricks are nothing more than classes that have an associated view that is rendered in the editor with its data.

To help you get started, there is a `make:mason-brick` command that will create a new brick for you with the necessary class and blade template in the paths specified in the config file.

```bash
php artisan make:mason-brick Section
```

This will create a new brick in the `App\Mason` namespace with the class `Section` and a preview and index blade template in the `resources/views/mason` directory. Bricks follow the same conventions as Filament RichEditor custom blocks.

```php
use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section as FilamentSection;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Throwable;

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
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 20h.01M4 20h.01M8 20h.01M12 20h.01M16 20h.01M20 4h.01M4 4h.01M8 4h.01M12 4h.01M16 4v.01M4 9a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/></svg>');
    }

    /**
     * @throws Throwable
     */
    public static function toHtml(array $config, ?array $data = null): ?string
    {
        return view('mason::bricks.section.index', [
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

## Rendering Content

You are free to render the content however you see fit. The data is stored in the database as JSON, so you can use the data however you see fit. But the plugin offers a helper method for converting the data to HTML should you choose to use it.

Similar to the form field and entry components, the helper needs to know what bricks are available. You can pass the bricks to the helper as the second argument. See, above about creating a collection of bricks. This will help keep your code DRY.

```php
{!! mason(content: $post->content, bricks: \App\Mason\BrickCollection::make())->toHtml() !!}
```

There is also a dedicated Render that can be used if you need more control over the rendering process.

```php
use Awcodes\Mason\Support\MasonRenderer;

$renderer = MasonRenderer::make($content)->bricks(\App\Mason\BrickCollection::make());

$renderer->toHtml()
$renderer->toUnsafeHtml();
$renderer->toArray();
$renderer->toText();
```

## Faking Content

When testing, you may want to fake some Mason content. There is a helper method for that as well.

```php
use Awcodes\Mason\Support\Faker;

Faker::make()
    ->brick(
        id: 'section',
        config: [
            'background_color' => 'white',
            'text' => '<h2>This is a heading</h2><p>Just some random text for a paragraph</p>',
            'image' => null,
        ]
    )
    ->brick(
        id: 'section',
        config: [
            'background_color' => 'primary',
            'text' => '<h2>This is a heading</h2><p>Just some random text for a paragraph</p>',
            'image' => null,
        ]
    )
    ->asJson(),
```

<!-- [docs_end] -->

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Adam Weston](https://github.com/awcodes)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
