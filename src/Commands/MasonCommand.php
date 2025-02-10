<?php

namespace Awcodes\Mason\Commands;

use Illuminate\Console\Command;

class MasonCommand extends Command
{
    public $signature = 'mason';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
