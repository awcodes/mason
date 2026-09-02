<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="stylesheet" href="{{ asset('workbench/app.css') }}" />
    </head>
    <body class="font-body bg-white text-gray-900">
        <header class="border-b border-gray-200">
            <div class="mx-auto flex w-full max-w-5xl items-center justify-between px-6 py-4">
                <a href="{{ route('pages.index') }}" class="font-display font-semibold">
                    {{ config('app.name') }}
                </a>

                <a href="{{ url('admin') }}" class="text-sm text-gray-500 underline">
                    Admin
                </a>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>
    </body>
</html>
