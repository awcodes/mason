<div
    x-show="outlineSheetOpen"
    x-cloak
    x-transition:enter="transition duration-200 ease-out"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition duration-150 ease-in"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="mason-outline-sheet-overlay"
    x-on:click.self="closeOutlineSheet()"
    x-on:keydown.escape.window="closeOutlineSheet()"
>
    <div
        x-show="outlineSheetOpen"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        x-trap="outlineSheetOpen"
        class="mason-outline-sheet"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $attributes->get('id') }}-title"
        data-focus="mason-outline-sheet"
    >
        <div class="mason-outline-sheet-header">
            <h2
                id="{{ $attributes->get('id') }}-title"
                class="mason-outline-sheet-title"
            >
                {{ trans('mason::mason.outline.label') }}
            </h2>
            <button
                type="button"
                class="mason-outline-sheet-close"
                x-on:click="closeOutlineSheet()"
                title="{{ trans('mason::mason.outline.close') }}"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    class="size-5"
                >
                    <path
                        d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"
                    />
                </svg>
            </button>
        </div>

        <x-mason::outline />
    </div>
</div>
