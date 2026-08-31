<?php

declare(strict_types=1);

namespace Awcodes\Mason;

use Awcodes\Mason\Commands\MakeBrickCommand;
use Awcodes\Mason\Commands\UpgradeBricksCommand;
use Awcodes\Mason\Testing\TestsMason;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Livewire\Features\SupportTesting\Testable;
use ReflectionException;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MasonServiceProvider extends PackageServiceProvider
{
    public static string $name = 'mason';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasRoute('web')
            ->hasCommands([
                MakeBrickCommand::class,
                UpgradeBricksCommand::class,
            ]);
    }

    public function packageRegistered(): void {}

    /**
     * @throws ReflectionException
     */
    public function packageBooted(): void
    {
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        if (app()->runningInConsole()) {
            foreach (app(abstract: Filesystem::class)->files(directory: __DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path(path: "stubs/filament/mason/{$file->getFilename()}"),
                ], groups: 'mason-stubs');
            }
        }

        Blade::directive(
            name: 'mason',
            // Compiles to the mason() helper rather than the constructor: the
            // constructor takes only $content, so @mason($content, $bricks)
            // silently discarded the brick list and rendered nothing for it.
            handler: fn ($expression): string => "<?php echo mason({$expression})->toHtml(); ?>"
        );

        Blade::directive(
            name: 'masonStyles',
            handler: function (): string {
                $cssPath = __DIR__ . '/../resources/css/preview.css';
                $css = file_get_contents($cssPath);

                return '<style>' . $css . '</style>';
            }
        );

        Blade::directive(
            name: 'masonEntryStyles',
            handler: function (): string {
                $cssPath = __DIR__ . '/../resources/css/entry.css';
                $css = file_get_contents($cssPath);

                return '<style>' . $css . '</style>';
            }
        );

        Testable::mixin(new TestsMason);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'awcodes/mason';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            AlpineComponent::make(id: 'mason', path: __DIR__ . '/../resources/dist/mason.js'),
            AlpineComponent::make(id: 'mason-entry', path: __DIR__ . '/../resources/dist/mason-entry.js'),
        ];
    }
}
