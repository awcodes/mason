@props([
    'bricks' => [],
])

@php
    $brickData = array_map(fn ($brick) => ['label' => $brick::getLabel(), 'tags' => $brick::getTags()], $bricks);
@endphp

<div
    x-show="brickPickerOpen"
    x-cloak
    x-transition:enter="transition duration-200 ease-out"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition duration-150 ease-in"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="mason-brick-picker-overlay"
    x-on:click.self="closeBrickPicker()"
    x-on:keydown.escape.window="closeBrickPicker()"
>
    <div
        x-show="brickPickerOpen"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        class="mason-brick-picker-modal"
        x-data="{
            actions: @js($brickData),
            search: '',
            filterActions: function () {
                const q = this.search.toLowerCase()
                return this.actions
                    .filter((brick) =>
                        brick.label.toLowerCase().includes(q) ||
                        brick.tags.some((tag) => tag.toLowerCase().includes(q)),
                    )
                    .map((brick) => brick.label)
            },
        }"
    >
        <div class="mason-brick-picker-header">
            <h3 class="mason-brick-picker-title">
                {{ __('mason::mason.brick_picker.title') }}
            </h3>
            <button
                type="button"
                class="mason-brick-picker-close"
                x-on:click="closeBrickPicker()"
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

        <div class="mason-brick-picker-position">
            <button
                type="button"
                class="mason-brick-picker-position-btn"
                x-bind:class="{ 'active': brickPickerPosition === 'above' }"
                x-on:click="brickPickerPosition = 'above'"
            >
                {{ __('mason::mason.brick_picker.insert_above') }}
            </button>
            <button
                type="button"
                class="mason-brick-picker-position-btn"
                x-bind:class="{ 'active': brickPickerPosition === 'below' }"
                x-on:click="brickPickerPosition = 'below'"
            >
                {{ __('mason::mason.brick_picker.insert_below') }}
            </button>
        </div>

        <div class="mason-brick-picker-search">
            <x-filament::input.wrapper>
                <x-filament::input
                    x-ref="pickerSearch"
                    x-on:input.debounce.300ms="filterActions()"
                    placeholder="{{ trans('mason::mason.brick_search_placeholder') }}"
                    type="search"
                    x-model="search"
                ></x-filament::input>
            </x-filament::input.wrapper>
        </div>

        <div class="mason-brick-picker-bricks">
            @foreach ($bricks as $brick)
                <button
                    type="button"
                    class="mason-brick-picker-brick"
                    x-on:click="insertFromPicker(@js($brick::getId()))"
                    x-bind:class="{
                        'filtered': ! filterActions().includes(@js($brick::getLabel())),
                    }"
                >
                    @if (filled($brick::getIcon()))
                        <x-filament::icon
                            :icon="$brick::getIcon()"
                            class="h-5 w-5 shrink-0"
                        />
                    @endif

                    <span>{{ $brick::getLabel() }}</span>
                </button>
            @endforeach
        </div>
    </div>
</div>
