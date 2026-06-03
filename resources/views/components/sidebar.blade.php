@props([
    'bricks' => [],
    'hasGridActions' => false,
    'hasColorModeToggle' => false,
])

@php
    $brickIds = array_map(fn ($brick) => $brick::getLabel(), $bricks);
@endphp

<div
    @class([
        'mason-sidebar',
        'has-grid-actions' => $hasGridActions,
        'has-color-mode-toggle' => $hasColorModeToggle ?? false,
    ])
    {{ $attributes }}
>
    <x-mason::controls :has-color-mode-toggle="$hasColorModeToggle" />
    <div
        class="mason-actions"
        wire:ignore
        x-data="{
            actions: @js($brickIds),
            search: '',
            filterActions: function () {
                return this.actions.filter((name) =>
                    name.toLowerCase().includes(this.search.toLowerCase()),
                )
            },
        }"
    >
        <div class="mason-actions-search">
            <x-filament::input.wrapper>
                <x-filament::input
                    x-ref="search"
                    x-on:input.debounce.300ms="filterActions()"
                    placeholder="{{ trans('mason::mason.brick_search_placeholder') }}"
                    type="search"
                    x-model="search"
                ></x-filament::input>
            </x-filament::input.wrapper>
        </div>
        <div class="mason-actions-bricks">
            @foreach ($bricks as $brick)
                <div
                    draggable="true"
                    x-on:dragstart="
                        $event.dataTransfer.setData('brick', @js($brick::getId()))
                        $el.classList.add('dragging')
                    "
                    x-on:dragend="$el.classList.remove('dragging')"
                    class="mason-actions-brick"
                    x-on:open-modal.window="isLoading = false"
                    x-on:run-mason-commands.window="isLoading = false"
                    x-bind:class="{
                        'filtered': ! filterActions().includes(@js($brick::getLabel())),
                    }"
                >
                    @if (filled($brick::getIcon()))
                        <x-filament::icon
                            :icon="$brick::getIcon()"
                            class="h-5 w-5 shrink-0 mason-actions-brick-icon"
                        />
                    @endif

                    <span class="mason-actions-brick-label">{{ $brick::getLabel() }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
