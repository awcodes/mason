<?php

namespace Awcodes\Mason\Commands;

use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Console\Command;

use function Laravel\Prompts\text;

class MakeBrickCommand extends Command
{
    use CanManipulateFiles;

    public $signature = 'make:mason-brick {name?} {--F|force}';

    public $description = 'Scaffold a new Mason brick.';

    public function handle(): int
    {
        $brick = (string) str(
            string: $this->argument('name') ??
                text(
                    label: 'What is the brick name?',
                    placeholder: 'CustomBrick',
                    required: true,
                ),
        )
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->replace('/', '\\');

        $namespace = config('mason.generator.namespace');
        $viewsPath = config('mason.generator.views_path');

        $className = (string) str($brick)->afterLast('\\');
        $fullNamespace = str($namespace) . "\\{$className}";

        $brickLabel = (string) str($className)
            ->afterLast('.')
            ->kebab()
            ->replace(['-', '_'], ' ')
            ->ucfirst();

        $brickName = (string) str($className)
            ->afterLast('.')
            ->lcfirst();

        $view = (string) str($className)->kebab();

        $classPath = app_path(
            (string) str($className)
                ->prepend('/')
                ->prepend($namespace)
                ->replace('\\', '/')
                ->replace('//', '/')
                ->replace('App', '')
                ->append('.php')
        );

        $viewPath = resource_path(
            (string) str($view)
                ->prepend('/')
                ->prepend($viewsPath)
                ->prepend('/views/')
                ->replace('.', '/')
                ->replace('\\', '/')
                ->replace('//', '/')
                ->append('.blade.php')
        );

        $files = [
            $classPath,
            $viewPath,
        ];

        if (! $this->option('force') && $this->checkForCollision($files)) {
            return static::INVALID;
        }

        $this->copyStubToApp('brick-class', $classPath, [
            'namespace' => $namespace,
            'class_name' => $className,
            'brick_name' => $brickName,
            'brick_label' => $brickLabel,
            'path' => $viewsPath . '.' . $view,
        ]);

        $this->copyStubToApp('brick-view', $viewPath);

        $this->components->info("Mason brick [{$fullNamespace}] created successfully.");

        return self::SUCCESS;
    }
}
