<?php

namespace Awcodes\Mason;

use Awcodes\Mason\Concerns\HasBricks;
use Filament\Infolists\Components\Entry;

class MasonEntry extends Entry
{
    use HasBricks;

    protected string $view = 'mason::mason-entry';
}
