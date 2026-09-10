<?php

declare(strict_types=1);

namespace Awcodes\Mason;

use Awcodes\Mason\Concerns\HasBricks;
use Awcodes\Mason\Support\DataPayload;
use Closure;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Infolists\Components\Entry;
use Illuminate\Database\Eloquent\Model;

class MasonEntry extends Entry
{
    use HasBricks;
    use HasExtraInputAttributes;

    protected string $view = 'mason::mason-entry';

    protected string | Closure | null $previewLayout = null;

    /**
     * @var array<string, mixed> | Closure | null
     */
    protected array | Closure | null $data = null;

    public function previewLayout(string | Closure | null $layout): static
    {
        $this->previewLayout = $layout;

        return $this;
    }

    public function getPreviewLayout(): ?string
    {
        return $this->evaluate($this->previewLayout) ?? config('mason.entry.layout');
    }

    /**
     * Arbitrary context handed to every brick's toHtml() as its second argument.
     * Defaults to the entry's own record, so bricks can read it without setup.
     *
     * @param  array<string, mixed> | Closure | null  $data
     */
    public function data(array | Closure | null $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        if ($this->data !== null) {
            return $this->evaluate($this->data) ?? [];
        }

        // getRecord() walks up to the schema container for its record, and that
        // container is unset on a component not yet attached to one.
        $record = $this->getRecord(withContainerRecord: isset($this->container));

        return $record instanceof Model ? ['record' => $record] : [];
    }

    /**
     * The entry renders inside an iframe fed by a separate request, so its data
     * travels as an encrypted payload rather than in memory.
     */
    public function getEncodedData(): ?string
    {
        return DataPayload::encode($this->getData());
    }
}
