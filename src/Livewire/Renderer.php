<?php

namespace Awcodes\Mason\Livewire;

use Awcodes\Mason\Support\Helpers;
use Livewire\Attributes\Isolate;
use Livewire\Component;

class Renderer extends Component
{
    #[Isolate]
    public function getView(string $path, ?array $attrs): ?string
    {
        return Helpers::sanitizeLivewire(view($path, $attrs ?? [])->toHtml());
    }

    public function render(): string
    {
        return <<<'HTML'
        <div id="mason-brick-renderer"></div>
        HTML;
    }
}
