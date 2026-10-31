<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>{{ config('app.name') }}</title>

        <link rel="stylesheet" href="{{ asset('workbench/app.css') }}" />

        @masonStyles
    </head>
    <body class="font-body bg-white text-gray-900">
        <header data-mason-layout class="border-b border-gray-200">
            <div class="mx-auto flex w-full max-w-5xl items-center justify-between px-6 py-4">
                <span class="font-display font-semibold">{{ config('app.name') }}</span>
            </div>
        </header>

        <main>
            @include('mason::iframe-preview-content', ['blocks' => $blocks])
        </main>

        <footer data-mason-layout class="border-t border-gray-200">
            <div class="mx-auto w-full max-w-5xl px-6 py-8 text-sm text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}
            </div>
        </footer>
    </body>
</html>
