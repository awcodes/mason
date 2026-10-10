{{-- Where bricks are dropped into a container brick in the editor preview --}}
<div
    class="mason-drop-zone mason-drop-zone-nested @if ($isEmpty) mason-drop-zone-empty @endif"
    data-drop-parent="{{ $parent }}"
    data-drop-index="{{ $index }}"
>
    <button
        type="button"
        class="mason-drop-zone-add"
        data-action="add-into"
        title="{{ __('mason::mason.preview.add') }}"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
        >
            <path
                d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"
            />
        </svg>
        @if ($isEmpty)
            <span>{{ __('mason::mason.preview.placeholder') }}</span>
        @endif
    </button>
</div>
