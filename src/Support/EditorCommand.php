<?php

namespace Awcodes\Mason\Support;

use Illuminate\Contracts\Support\Arrayable;

readonly class EditorCommand implements Arrayable
{
    public function __construct(
        public readonly string $name,
        public readonly array $arguments = [],
    ) {}

    /**
     * @param string $name
     * @param array $arguments
     * @return EditorCommand
     */
    public static function make(string $name, array $arguments = []): static
    {
        return app(static::class, ['name' => $name, 'arguments' => $arguments]);
    }

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
