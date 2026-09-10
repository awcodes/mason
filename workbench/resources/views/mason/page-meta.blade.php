@props([
    'heading' => 'About this page',
    'show_url' => true,
    'record' => null,
])

<section class="page-meta font-body branded @container bg-white">
    <div class="mx-auto w-full max-w-5xl px-6 py-12">
        <h2
            class="font-display text-xl font-semibold tracking-tight text-gray-900"
        >
            {{ $heading }}
        </h2>

        @if ($record)
            <dl class="mt-6 grid grid-cols-1 gap-6 @2xl:grid-cols-3">
                <div>
                    <dt
                        class="text-xs font-medium tracking-wide text-gray-500 uppercase"
                    >
                        Title
                    </dt>
                    <dd class="mt-1 text-gray-900">{{ $record->title }}</dd>
                </div>

                @if ($show_url)
                    <div>
                        <dt
                            class="text-xs font-medium tracking-wide text-gray-500 uppercase"
                        >
                            URL
                        </dt>
                        <dd class="mt-1 text-gray-900">
                            /pages/{{ $record->slug }}
                        </dd>
                    </div>
                @endif

                <div>
                    <dt
                        class="text-xs font-medium tracking-wide text-gray-500 uppercase"
                    >
                        Last updated
                    </dt>
                    <dd class="mt-1 text-gray-900">
                        {{ $record->updated_at?->format('j M Y') ?? '—' }}
                    </dd>
                </div>
            </dl>
        @else
            {{-- No record in scope: the editor preview renders this branch. --}}
            <p
                class="mt-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center text-sm text-gray-500"
            >
                The page title, URL and date appear here once this content is
                rendered against a page.
            </p>
        @endif
    </div>
</section>
