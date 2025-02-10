<?php

namespace Awcodes\Mason;

use Illuminate\Contracts\Support\Arrayable;

readonly class EditorCommand implements Arrayable
{
    public function __construct(
        public string $name,
        public array $arguments = [],
    ) {}

    /**
     * @return array{name: string, arguments: array}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'arguments' => $this->arguments,
        ];
    }
}
