<div class="mason-outline" wire:ignore>
    <p class="mason-outline-empty" x-show="! outlineItems().length">
        {{ trans('mason::mason.outline.empty') }}
    </p>

    <ol
        class="mason-outline-list"
        x-init="initOutline($el)"
        aria-label="{{ trans('mason::mason.outline.label') }}"
    >
        <template
            x-for="(item, index) in outlineItems()"
            x-bind:key="index"
        >
            <li
                data-outline-item
                class="mason-outline-item"
                x-bind:data-outline-index="index"
                x-bind:class="{ 'selected': selectedBlockIndex === index }"
            >
                <span
                    data-outline-handle
                    class="mason-outline-handle"
                    title="{{ trans('mason::mason.outline.drag') }}"
                    aria-hidden="true"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            d="M7 4a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 6a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm-1.5 7.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM16 4a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm-1.5 7.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3ZM16 16a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"
                        />
                    </svg>
                </span>
                <button
                    type="button"
                    class="mason-outline-select"
                    title="{{ trans('mason::mason.outline.keyboard_hint') }}"
                    x-on:click="focusBlock(index)"
                    x-on:keydown.alt.arrow-up.prevent="moveOutlineBlock(index, index - 1, $el)"
                    x-on:keydown.alt.arrow-down.prevent="moveOutlineBlock(index, index + 1, $el)"
                >
                    <span
                        class="mason-outline-icon"
                        x-show="item.icon"
                        x-html="item.icon"
                    ></span>
                    <span
                        class="mason-outline-label"
                        x-text="item.label"
                    ></span>
                </button>
            </li>
        </template>
    </ol>
</div>
