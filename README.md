# Mason

A simple block based drag and drop page / document builder field for Filament.

<img src="https://res.cloudinary.com/aw-codes/image/upload/w_1200,f_auto,q_auto/plugins/mason/awcodes-mason.jpg" alt="mason opengraph image" width="1200" height="auto" class="filament-hidden" style="width: 100%;" />

[![Latest Version on Packagist](https://img.shields.io/packagist/v/awcodes/mason.svg?style=flat-square)](https://packagist.org/packages/awcodes/mason)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/awcodes/mason/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/awcodes/mason/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/awcodes/mason/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/awcodes/mason/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/awcodes/mason.svg?style=flat-square)](https://packagist.org/packages/awcodes/mason)

## Installation

You can install the package via composer:

```bash
composer require awcodes/mason
```

In an effort to align with Filament's theming methodology you will need to use a custom theme to use this plugin.

> [!IMPORTANT]
> If you have not set up a custom theme and are using a Panel follow the instructions in the [Filament Docs](https://filamentphp.com/docs/3.x/panels/themes#creating-a-custom-theme) first.

1. Import the plugin's stylesheet into your panel's custom theme css file. This will most likely be at `resources/css/filament/admin/theme.css`.

```css
@import '/vendor/awcodes/mason/resources/css/index.css';
```

2. Add the plugin's views to your `resources/css/filament/admin/tailwind.config.js` file.

```js
content: [
    './vendor/awcodes/mason/resources/**/*.blade.php',
]
```

3. Rebuild your custom theme.

```bash
npm run build
```

## Configuration

You can publish the config file with:

```bash
php artisan vendor:publish --tag="mason-config"
```

This is the contents of the published config file:

```php
return [
    'generator' => [
        'namespace' => 'App\\Mason',
        'views_path' => 'mason',
    ],
];
```

## Usage

> [!IMPORTANT]
> Since Mason uses json to store its data in the database you will need to make sure your model's field is cast to 'array' or 'json'.

### Form Field

In your Filament forms you should use the `Mason` component. The `Mason` component accepts a `name` prop which should be the name of the field in your model, and requires an array of actions that make up the 'bricks' available to the editor.

```php
use Awcodes\Mason\Mason;
use Awcodes\Mason\Bricks\Section;

->schema([
    Mason::make('content')
        ->bricks([
            Section::make(),
        ])
        // optional
        ->placeholder('Drag and drop bricks to get started...'),
])
```

### Infolist Entry

In your Filament infolist you should use the `MasonEntry` component. The `Mason` component accepts a `name` prop which should be the name of the field in your model.

```php
use Awcodes\Mason\MasonEntry;
use Awcodes\Mason\Bricks\Section;

->schema([
    MasonEntry::make('content')
        ->bricks([
            Section::make(),
        ])
])            
```

To keep from having to repeat yourself when assigning bricks to the editor and the entry it would help to create sets of bricks that make sense for their use case. Then you can just use that in the `bricks` method.

```php
class BrickCollection
{
    public static function make(): array
    {
        return [
            NewsletterSignup::make(),
            Section::make(),
            Cards::make(),
            SupportCenter::make(),
        ];
    }
}

Mason::make('content')
    ->bricks(BrickCollection::make())

MasonEntry::make('content')
    ->bricks(BrickCollection::make())
```
     
## Creating Bricks

Bricks are nothing more than Filament actions that have an associated view that is rendered in the editor with its data.

To help you get started there is a `make:mason-brick` command that will create a new brick for you with the necessary class and blade template in the paths specified in the config file.

```bash
php artisan make:mason-brick Section
```

This will create a new brick in the `App\Mason` namespace with the class `Section` and a blade template in the `resources/views/mason` directory. Bricks follow the same conventions as Filament actions. The important things to note are the `fillForm` method and the `action` method. These are how the action interacts with the editor. For bricks that do not have data you can remove the `fillForm` method and the `form` method from the brick and it will be inserted into the editor as is.

```php
use Awcodes\Mason\Brick;use Awcodes\Mason\Mason;use Awcodes\Mason\Support\EditorCommand;use Filament\Forms\Components\FileUpload;use Filament\Forms\Components\Radio;use Filament\Forms\Components\RichEditor;

class Section
{
    public static function make(): Brick
    {
        return Brick::make('section')
            ->label('Section')
            ->modalHeading('Section Settings')
            ->icon('heroicon-o-cube')
            ->slideOver()
            ->fillForm(fn (array $arguments): array => [
                'background_color' => $arguments['background_color'] ?? 'white',
                'text' => $arguments['text'] ?? null,
                'image' => $arguments['image'] ?? null,
            ])
            ->form([
                Radio::make('background_color')
                    ->options([
                        'white' => 'White',
                        'gray' => 'Gray',
                        'primary' => 'Primary',
                    ])
                    ->inline()
                    ->inlineLabel(false),
                FileUpload::make('image'),
                RichEditor::make('text'),
            ])
            ->action(function (array $arguments, array $data, Mason $component) {
                $component->runCommands(
                    [
                        new EditorCommand(
                            name: 'setBrick',
                            arguments: [[
                                'identifier' => 'section',
                                'values' => $data,
                                'path' => 'bricks.section',
                                'view' => view('bricks.section', $data)->toHtml(),
                            ]],
                        ),
                    ],
                    editorSelection: $arguments['editorSelection'],
                );
            });
    }
}
```

## Rendering Content

You are free to render the content however you see fit. The data is stored in the database as json so you can use the data however you see fit. But the plugin offers a helper method for converting the data to html should you choose to use it.

Similar to the form field and entry components the helper needs to know what bricks are available. You can pass the bricks to the helper as the second argument. See, above about creating a collection of bricks. This will help keep your code DRY.

```html
{!! mason($post->content, \App\Mason\BrickCollection::make())->toHtml() !!}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Adam Weston](https://github.com/awcodes)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
