{{-- Line with a centred add button in a drop zone; opens the brick picker at the zone's position --}}
<button
    type="button"
    class="mason-inserter"
    data-action="add-at"
    title="{{ __('mason::mason.preview.add') }}"
    aria-label="{{ __('mason::mason.preview.add') }}"
>
    <span class="mason-inserter-icon" aria-hidden="true">
        @include('mason::components.icons.plus')
    </span>
</button>
