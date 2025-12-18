<?php

declare(strict_types=1);

namespace Awcodes\Mason\Commands;

use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Laravel\Prompts\text;

class MakeBrickCommand extends Command
{
    use CanManipulateFiles;

    public $signature = 'make:mason-brick {name?} {--F|force}';

    public $description = 'Scaffold a new Mason brick.';

    public function handle(): int
    {
        $brick = (string) Str::of(
            $this->argument('name') ??
            text(
                label: 'What is the brick name?',
                placeholder: 'CustomBrick',
                required: true,
            )
        )
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->replace('/', '\\');

        $namespace = config('mason.generator.namespace');
        $viewsPath = config('mason.generator.views_path');

        $className = (string) Str::of($brick)->afterLast('\\');
        $brickNamespace = Str::of($brick)->contains('\\')
            ? (string) Str::of($brick)->beforeLast('\\')
            : '';

        $fullNamespace = $brickNamespace !== '' && $brickNamespace !== '0'
            ? "{$namespace}\\{$brickNamespace}\\{$className}"
            : "{$namespace}\\{$className}";

        $brickLabel = (string) Str::of($className)
            ->afterLast('.')
            ->kebab()
            ->replace(['-', '_'], ' ')
            ->ucfirst();

        $brickName = Str::of($brick)
            ->explode('\\')
            ->map(fn ($segment) => Str::kebab($segment))
            ->implode('.');

        $view = Str::of($brick)
            ->explode('\\')
            ->map(fn ($segment) => Str::kebab($segment))
            ->implode('.');

        $classPath = app_path(
            (string) Str::of($brick)
                ->prepend($namespace . '\\')
                ->replace('\\', '/')
                ->replace('//', '/')
                ->replace('App', '')
                ->append('.php')
        );

        $previewPath = resource_path(
            (string) Str::of($view)
                ->prepend('views/' . $viewsPath . '/')
                ->replace('.', '/')
                ->replace('//', '/')
                ->append('/preview.blade.php')
        );

        $htmlPath = resource_path(
            (string) Str::of($view)
                ->prepend('views/' . $viewsPath . '/')
                ->replace('.', '/')
                ->replace('//', '/')
                ->append('/index.blade.php')
        );

        $files = [$classPath, $previewPath, $htmlPath];

        if (! $this->option('force') && $this->checkForCollision($files)) {
            return static::INVALID;
        }

        File::ensureDirectoryExists(dirname($classPath));
        File::ensureDirectoryExists(dirname($previewPath));
        File::ensureDirectoryExists(dirname($htmlPath));

        $this->copyStubToApp('brick-class', $classPath, [
            'namespace' => $brickNamespace !== '' && $brickNamespace !== '0'
                ? $namespace . '\\' . $brickNamespace
                : $namespace,
            'class_name' => $className,
            'brick_name' => $brickName,
            'brick_label' => $brickLabel,
            'preview_path' => $viewsPath . '.' . $view . '.preview',
            'html_path' => $viewsPath . '.' . $view . '.index',
        ]);

        $this->copyStubToApp('brick-view', $previewPath);
        $this->copyStubToApp('brick-view', $htmlPath);

        $this->components->info("Mason brick [{$fullNamespace}] created successfully.");

        return self::SUCCESS;
    }
}
