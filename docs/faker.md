---
title: Faking content
description: Generate Mason content for seeders and tests without going through the editor.
---

# Faking content

`Faker` builds Mason content programmatically, which is what you want in seeders and tests rather than hand-writing JSON.

```php
use Awcodes\Mason\Support\Faker;

Faker::make()
    ->brick(
        id: 'section',
        config: [
            'background_color' => 'white',
            'text' => '<h2>This is a heading</h2><p>Just some random text for a paragraph</p>',
            'image' => null,
        ],
    )
    ->brick(
        id: 'section',
        config: [
            'background_color' => 'primary',
            'text' => '<h2>Another heading</h2><p>More placeholder copy</p>',
            'image' => null,
        ],
    )
    ->asJson();
```

`brick()` takes the brick's `getId()` and the config its form would have saved, and calls append in order.

Finish with one of three outputs:

| Method | Returns |
|---|---|
| `asJson()` | The structure to store on the model. |
| `asHtml()` | Rendered HTML. |
| `asText()` | Plain text. |

`asJson()` is what you want for a factory or seeder, since it matches what the editor would have written.
