{{-- Line with a centred add button in a drop zone; opens the brick picker at the zone's position --}}
<button
    type="button"
    class="mason-inserter"
    data-action="add-at"
    title="{{ __('mason::mason.preview.add') }}"
    aria-label="{{ __('mason::mason.preview.add') }}"
>
    <span class="mason-inserter-icon" aria-hidden="true">
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
        >
            <path
                d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"
            />
        </svg>
    </span>
</button>
