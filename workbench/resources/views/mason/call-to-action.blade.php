@props([
    'heading' => null,
    'text' => null,
    'button_label' => null,
    'button_url' => null,
    'new_tab' => false,
])

<section class="call-to-action font-body branded @container bg-gray-100 text-gray-900">
    <div class="@3xl:py-16 mx-auto w-full max-w-3xl px-6 py-10 text-center">
        @if ($heading)
            <h2 {{ mason_part('heading') }} class="font-display text-2xl font-semibold tracking-tight">
                {{ $heading }}
            </h2>
        @endif

        @if ($text)
            <p {{ mason_part('text') }} class="mt-3 text-gray-600">{{ $text }}</p>
        @endif

        @if ($button_label)
            <a
                {{ mason_part('button_label') }}
                href="{{ $button_url ?: '#' }}"
                @if ($new_tab) target="_blank" rel="noopener" @endif
                class="bg-primary-500 mt-6 inline-block rounded-md px-5 py-2.5 text-sm font-semibold text-white"
            >
                {{ $button_label }}
            </a>
        @endif
    </div>
</section>
