---
title: Container bricks
description: Bricks that hold other bricks, such as columns or a card grid.
---

# Container bricks

A container brick holds other bricks. Its own form has its settings (e.g. how many columns), and the bricks inside it are added, edited, moved and removed in the editor like any other brick: drag them in, use the drop zone's add button, or move them out again.

## Creating one

Implement `ContainsBricks`. Mason renders the bricks inside and hands them over, so that the editor can wrap each of them in its controls. The container only decides where they go:

```php
use Awcodes\Mason\Brick;
use Awcodes\Mason\Contracts\ContainsBricks;
use Awcodes\Mason\Support\BrickChildren;

class Columns extends Brick implements ContainsBricks
{
    public static function getId(): string
    {
        return 'columns';
    }

    public static function toHtmlWithChildren(array $config, BrickChildren $children, ?array $data = null): ?string
    {
        return view('mason.columns', [
            'columns' => $config['columns'] ?? 2,
            'children' => $children,
        ])->render();
    }

    /**
     * Null allows every brick of the field.
     */
    public static function getAllowedChildren(): ?array
    {
        return [Section::class, 'image'];
    }
}
```

```blade
<div class="grid" style="grid-template-columns: repeat({{ $columns }}, 1fr)">
    @foreach ($children as $child)
        <div>{{ $child }}</div>
    @endforeach

    {{ $children->dropZone() }}
</div>
```

Output `dropZone()` after the bricks. In the editor it is where bricks are dropped into the container, everywhere else it is empty. A container that wraps nothing can output `{{ $children }}`, which includes the drop zone.

Each child gives access to the stored brick:

- `$child->getId()` and `$child->getConfig()`
- `$child->getAttribute('layout.span', 1)` for attributes the container keeps on its bricks, e.g. their placement

`toHtml()` is not called for a container brick. `getAllowedChildren()` takes classes or ids; inserting or moving other bricks into the container is refused, in the editor and on the server.

## Storage

The bricks inside a container are stored under its `content` key:

```json
{
    "type": "masonBrick",
    "attrs": {"id": "columns", "config": {"columns": 2}},
    "content": [
        {"type": "masonBrick", "attrs": {"id": "section", "config": {}}}
    ]
}
```

Editing a container changes its config and keeps the bricks inside it, as well as attributes other code keeps on it.

## Paths

Bricks are addressed by a path of indexes: `2` is the third brick at the top level, `2.0` the first brick inside it. `BrickCommand` takes paths wherever it took an index, and `insertBrick()` and `moveBrick()` take the path of the container to insert into or move into:

```php
$component->executeCommands([
    BrickCommand::insertBrick($brick, position: 0, parent: '2'),
    BrickCommand::moveBrick('2.0', to: 1, parent: ''),
]);
```

`Awcodes\Mason\Support\BrickTree` reads and changes nested content outside the field: `get()`, `children()`, `insert()`, `update()`, `remove()`, `move()`, and `walk()` and `map()` to visit every brick, parents before their children.
