@props([
    'background_color' => 'white',
    'heading' => null,
    'text' => null,
])

<section
    @class([
        'hero font-body branded @container',
        match ($background_color) {
            'primary' => 'bg-primary-500 text-white',
            'gray' => 'bg-gray-100 text-gray-900',
            default => 'bg-white text-gray-900',
        },
    ])
>
    <div class="@3xl:py-20 mx-auto w-full max-w-5xl px-6 py-12 text-center">
        @if ($heading)
            <h1 class="@3xl:text-5xl font-display text-3xl font-semibold tracking-tight">
                {{ $heading }}
            </h1>
        @endif

        @if ($text)
            <div class="prose mx-auto mt-6 max-w-2xl">
                {!! $text !!}
            </div>
        @endif
    </div>
</section>
