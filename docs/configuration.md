---
title: Configuration
description: Publish Mason's config file and set the generator paths, default layouts and route middleware.
---

# Configuration

Publish the config file with:

```bash
php artisan vendor:publish --tag="mason-config"
```

```php
return [
    'generator' => [
        'namespace' => 'App\\Mason',
        'views_path' => 'mason',
    ],
    'preview' => [
        'layout' => 'mason::iframe-preview',
    ],
    'entry' => [
        'layout' => 'mason::iframe-entry',
    ],
    'routes' => [
        'middleware' => ['web', 'auth'],
    ],
];
```

| Key | Purpose |
|---|---|
| `generator.namespace` | Namespace `make:mason-brick` writes brick classes into. |
| `generator.views_path` | Directory under `resources/views` for generated brick templates. |
| `preview.layout` | Default preview layout for the `Mason` field. |
| `entry.layout` | Default preview layout for `MasonEntry`. |
| `routes.middleware` | Middleware applied to Mason's internal routes. |

Both layout defaults can be overridden per field with `previewLayout()` — see [Form field](fields/form.md).

## Auth guards

Mason's internal routes run through the `web` and `auth` middleware. If your panel uses a different guard, adjust the middleware so those routes authenticate against it:

```php
'routes' => [
    'middleware' => ['web', 'auth:admin'],
],
```
