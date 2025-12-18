<?php

declare(strict_types=1);

namespace Awcodes\Mason\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

class UpgradeBricksCommand extends Command
{
    public $signature = 'mason:upgrade-bricks {--table=} {--column=}';

    public $description = 'Upgrade Mason brick schema from 0.x to 1.x';

    public function handle(): int
    {
        $table = $this->option('table') ?? text(
            label: 'Which table would you like to update?',
            placeholder: 'posts',
            required: true,
            validate: fn (string $value) => Schema::hasTable($value) ? null : "Table '{$value}' does not exist.",
        );

        $column = $this->option('column') ?? text(
            label: 'Which column would you like to update?',
            placeholder: 'content',
            required: true,
            validate: fn (string $value) => Schema::hasColumn($table, $value) ? null : "Column '{$value}' does not exist on table '{$table}'.",
        );

        if (! confirm("Are you sure you want to update the '{$column}' column in the '{$table}' table? This will overwrite existing data. Please make sure you have a backup.")) {
            return self::FAILURE;
        }

        $count = DB::table($table)->count();

        if ($count === 0) {
            info('No records found to upgrade.');

            return self::SUCCESS;
        }

        spin(
            callback: function () use ($table, $column): void {
                DB::table($table)->orderBy('id')->chunkById(100, function ($records) use ($table, $column): void {
                    foreach ($records as $record) {
                        $content = $record->{$column};

                        if (blank($content)) {
                            continue;
                        }

                        if (is_string($content)) {
                            $content = json_decode($content, true);
                        }

                        if (! is_array($content)) {
                            continue;
                        }

                        $updatedContent = $this->upgradeNodes($content);

                        DB::table($table)
                            ->where('id', $record->id)
                            ->update([$column => json_encode($updatedContent)]);
                    }
                });
            },
            message: 'Upgrading bricks...'
        );

        info('Bricks upgraded successfully.');

        return self::SUCCESS;
    }

    protected function upgradeNodes(array $node): array
    {
        if (isset($node['type']) && $node['type'] === 'masonBrick') {
            $attrs = $node['attrs'] ?? [];

            if (isset($attrs['identifier'])) {
                $attrs['id'] = $attrs['identifier'];
                unset($attrs['identifier']);
            }

            if (isset($attrs['values'])) {
                $attrs['config'] = $attrs['values'];
                unset($attrs['values']);
            }

            unset($attrs['path']);

            $node['attrs'] = $attrs;
        }

        if (isset($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $key => $childNode) {
                if (is_array($childNode)) {
                    $node['content'][$key] = $this->upgradeNodes($childNode);
                }
            }
        }

        return $node;
    }
}
