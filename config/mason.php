<?php

declare(strict_types=1);

return [
    'generator' => [
        'namespace' => 'App\\Mason',
        'views_path' => 'mason',
    ],
    'preview' => [
        'layout' => 'mason::iframe-preview', // Set to your layout view path, e.g., 'layouts.preview'
    ],
    'entry' => [
        'layout' => 'mason::iframe-entry', // Set to your layout view path, e.g., 'layouts.entry'
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard
    |--------------------------------------------------------------------------
    |
    | This option controls which authentication guard Mason routes will use.
    | If set to null, the default guard from auth.php will be used.
    | This is useful when using Mason with Filament admin panels that use
    | custom guards like 'admin', 'web', 'sanctum', etc.
    |
    | Example: 'admin' for Filament admin panel
    |
    */
    'guard' => null,
];
