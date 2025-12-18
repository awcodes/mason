<img src="https://res.cloudinary.com/aw-codes/image/upload/w_1200,f_auto,q_auto/plugins/mason/awcodes-mason.jpg" alt="mason opengraph image" width="1200" height="auto" class="filament-hidden" style="width: 100%;" />

[![Latest Version on Packagist](https://img.shields.io/packagist/v/awcodes/mason.svg?style=flat-square)](https://packagist.org/packages/awcodes/mason)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/awcodes/mason/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/awcodes/mason/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/awcodes/mason.svg?style=flat-square)](https://packagist.org/packages/awcodes/mason)

# Mason

A simple block based drag and drop page / document builder field for Filament.

## Compatibility

| Package Version | Filament Version |
|-----------------|------------------|
| 0.x             | 3.x              |
| 1.x             | 4.x              |

## Installation

You can install the package via composer:

```bash
composer require awcodes/mason
```

In an effort to align with Filament's theming methodology you will need to use a custom theme to use this plugin.

> [!IMPORTANT]
> If you have not set up a custom theme and are using Filament Panels follow the instructions in the [Filament Docs](https://filamentphp.com/docs/4.x/styling/overview#creating-a-custom-theme) first. The following applies to both the Panels Package and the standalone Tables package.

After setting up a custom theme add the plugin's css to your theme css file or your app's css file if using the standalone forms package.

```css
@import '../../../../vendor/awcodes/mason/resources/css/plugin.css';

@source '../../../../vendor/awcodes/mason/resources/**/*.blade.php';
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
            Section::class,
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
            Section::class,
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
     
## Creating Bricks

Bricks are nothing more than Filament actions that have an associated view that is rendered in the editor with its data.

To help you get started there is a `make:mason-brick` command that will create a new brick for you with the necessary class and blade template in the paths specified in the config file.

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

    public static function getPreviewLabel(array $config): string
    {
        return static::getLabel();
    }

    public static function getIcon(): string | Heroicon | Htmlable | null
    {
        return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 20h.01M4 20h.01M8 20h.01M12 20h.01M16 20h.01M20 4h.01M4 4h.01M8 4h.01M12 4h.01M16 4v.01M4 9a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/></svg>');
    }

    /**
     * @throws Throwable
     */
    public static function toPreviewHtml(array $config): ?string
    {
        return static::toHtml($config, []);
    }

    /**
     * @throws Throwable
     */
    public static function toHtml(array $config, array $data): ?string
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

You are free to render the content however you see fit. The data is stored in the database as json so you can use the data however you see fit. But the plugin offers a helper method for converting the data to html should you choose to use it.

Similar to the form field and entry components the helper needs to know what bricks are available. You can pass the bricks to the helper as the second argument. See, above about creating a collection of bricks. This will help keep your code DRY.

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

When testing you may want to fake some Mason content. There is a helper method for that as well.

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
