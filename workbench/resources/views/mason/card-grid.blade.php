@props([
    'cards' => [],
])

<section class="card-grid font-body branded @container bg-white text-gray-900">
    <div class="@3xl:py-12 mx-auto w-full max-w-5xl px-6 py-8">
        <div class="@3xl:grid-cols-3 grid gap-6">
            @foreach ($cards as $card)
                <article class="rounded-lg border border-gray-200 p-6 shadow-sm">
                    <h3 class="font-display text-lg font-semibold">
                        {{ $card['heading'] ?? '' }}
                    </h3>

                    @if (filled($card['body'] ?? null))
                        <div class="prose prose-sm mt-3 max-w-none">
                            {!! $card['body'] !!}
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
