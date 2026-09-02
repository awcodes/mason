<x-layouts.app>
    <div class="mx-auto w-full max-w-5xl px-6 py-12">
        <h1 class="font-display text-3xl font-semibold tracking-tight">Pages</h1>

        <ul class="mt-6 space-y-2">
            @foreach ($pages as $page)
                <li>
                    <a href="{{ route('pages.show', $page) }}" class="text-primary-600 underline">
                        {{ $page->title }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</x-layouts.app>
