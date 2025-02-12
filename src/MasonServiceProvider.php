<?php

namespace Awcodes\Mason;

use Awcodes\Mason\Livewire\Renderer;
use Awcodes\Mason\Support\Helpers;
use Awcodes\Mason\Testing\TestsMason;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MasonServiceProvider extends PackageServiceProvider
{
    public static string $name = 'mason';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasViews()
            ->hasTranslations();
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/mason/{$file->getFilename()}"),
                ], 'mason-stubs');
            }
        }

        Blade::directive('mason', fn ($expression) => "<?php echo (new Awcodes\Mason\Support\Converter({$expression}))->toHtml(); ?>");

        Livewire::component('mason.renderer', Renderer::class);

        if (! Helpers::isAuthRoute()) {
            FilamentView::registerRenderHook(
                name: 'panels::body.end',
                hook: fn (): string => Blade::render('@livewire("mason.renderer")')
            );
        }

        // Testing
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
            AlpineComponent::make('mason', __DIR__ . '/../resources/dist/mason.js'),
        ];
    }
}
