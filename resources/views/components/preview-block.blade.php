{{-- A brick in the editor preview with its controls; also used for the bricks inside a container brick --}}
<div
    class="mason-block"
    draggable="true"
    data-block-index="{{ $block["index"] }}"
    data-block-path="{{ $block["path"] }}"
    data-brick-id="{{ $block["id"] }}"
    data-config="{{ json_encode($block["config"]) }}"
    data-total-blocks="{{ $total }}"
    @if (is_array($block["accepts"] ?? null))
        data-accepts="{{ implode(",", $block["accepts"]) }}"
    @endif
>
    <div class="mason-block-controls" data-focus="mason-block-controls">
        <button
            class="mason-block-btn"
            title="Move Up"
            data-action="move-up"
            data-block-index="{{ $block["index"] }}"
            data-total-blocks="{{ $total }}"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <title>{{ __("mason::mason.preview.move_up") }}</title>
                <polyline points="18 15 12 9 6 15"></polyline>
            </svg>
        </button>
        <button
            class="mason-block-btn"
            title="Move Down"
            data-action="move-down"
            data-block-index="{{ $block["index"] }}"
            data-total-blocks="{{ $total }}"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <title>{{ __("mason::mason.preview.move_down") }}</title>
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>
        <button
            class="mason-block-btn"
            title="{{ __("mason::mason.preview.add") }}"
            data-action="add"
            data-focus-action="mason-add-brick"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                class="size-5"
            >
                <title>{{ __("mason::mason.preview.add") }}</title>
                <path
                    d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"
                />
            </svg>
        </button>
        <button class="mason-block-btn" title="Edit" data-action="edit">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                class="size-5"
            >
                <title>{{ __("mason::mason.preview.edit") }}</title>
                <path
                    d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z"
                />
                <path
                    d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z"
                />
            </svg>
        </button>
        <button class="mason-block-btn" title="Delete" data-action="delete">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                class="size-5"
            >
                <title>{{ __("mason::mason.preview.delete") }}</title>
                <path
                    fill-rule="evenodd"
                    d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z"
                    clip-rule="evenodd"
                />
            </svg>
        </button>
    </div>
    <div class="mason-block-content">
        {!! $block["html"] ?? "" !!}
    </div>
</div>
