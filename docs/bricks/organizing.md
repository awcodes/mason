---
title: Organizing bricks
description: Reuse brick lists, group them in the sidebar, and control their order.
---

# Organizing bricks

## Reusable collections

Both the field and the entry need the same brick list, and repeating it invites them to drift apart. Put it in one place:

```php
class BrickCollection
{
    public static function make(): array
    {
        return [
            NewsletterSignup::class,
            Section::class,
            Cards::class,
            SupportCenter::class,
        ];
    }
}
```

```php
Mason::make('content')->bricks(BrickCollection::make());

MasonEntry::make('content')->bricks(BrickCollection::make());
```

The same list is what [the renderer](rendering.md) needs on the front end, so a collection keeps all three in step.

## Groups

Wrap bricks in a `BrickGroup` to label and collapse them in the sidebar. Searching by name or tag expands any group holding a match.

```php
use Awcodes\Mason\BrickGroup;

Mason::make('content')
    ->bricks([
        BrickGroup::make('Content')
            ->bricks([
                Section::class,
                Grid::class,
            ]),
        BrickGroup::make('Marketing')
            ->bricks([
                Hero::class,
                CallToAction::class,
            ]),
        LeadForm::class,
    ]);
```

Groups and standalone bricks can be mixed freely in the same array, as `LeadForm` shows.

## Sorting

Bricks appear in the order you declare them. To sort by label instead:

```php
Mason::make('content')
    ->sortBricks()         // 'asc' by default
    ->bricks([...]);

Mason::make('content')
    ->sortBricks('desc')
    ->bricks([...]);
```

Sorting applies to the top-level array, so groups are sorted alongside standalone bricks by their respective labels — it does not reorder bricks inside a group.
