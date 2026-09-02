<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>{{ config('app.name') }}</title>

        <link rel="stylesheet" href="{{ asset('workbench/app.css') }}" />

        @masonEntryStyles
    </head>
    <body class="font-body bg-white text-gray-900">
        <main>
            @include('mason::iframe-entry-content', ['blocks' => $blocks])
        </main>
    </body>
</html>
