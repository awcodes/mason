@props([
    'bricks' => [],
    'hasGridActions' => false,
    'hasColorModeToggle' => false,
])

@php
    $brickData = [];
    foreach ($bricks as $item) {
        if ($item instanceof \Awcodes\Mason\BrickGroup) {
            foreach ($item->getBricks() as $brick) {
                $brickData[] = ['label' => $brick::getLabel(), 'tags' => $brick::getTags(), 'group' => $item->getLabel()];
            }
        } else {
            $brickData[] = ['label' => $item::getLabel(), 'tags' => $item::getTags(), 'group' => null];
        }
    }

    $initialOpenGroups = [];
    foreach ($bricks as $item) {
        if ($item instanceof \Awcodes\Mason\BrickGroup) {
            $initialOpenGroups[$item->getLabel()] = true;
        }
    }
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
            actions: @js($brickData),
            search: '',
            openGroups: @js($initialOpenGroups),
            filterActions: function () {
                const q = this.search.toLowerCase()
                return this.actions
                    .filter((brick) =>
                        brick.label.toLowerCase().includes(q) ||
                        brick.tags.some((tag) => tag.toLowerCase().includes(q)),
                    )
                    .map((brick) => brick.label)
            },
            groupHasMatch: function (label) {
                const q = this.search.toLowerCase()
                if (! q) return true
                return this.actions
                    .filter((b) => b.group === label)
                    .some((b) =>
                        b.label.toLowerCase().includes(q) ||
                        b.tags.some((t) => t.toLowerCase().includes(q)),
                    )
            },
            isGroupOpen: function (label) {
                if (this.search) return true
                return this.openGroups[label] !== false
            },
            toggleGroup: function (label) {
                this.openGroups[label] = ! this.isGroupOpen(label)
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
            @foreach ($bricks as $item)
                @if ($item instanceof \Awcodes\Mason\BrickGroup)
                    <div
                        class="mason-actions-group"
                        x-bind:class="{ 'filtered': ! groupHasMatch(@js($item->getLabel())) }"
                    >
                        <button
                            type="button"
                            class="mason-actions-group-header"
                            x-on:click="toggleGroup(@js($item->getLabel()))"
                        >
                            <span class="mason-actions-group-label">{{ $item->getLabel() }}</span>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="mason-actions-group-chevron"
                                x-bind:class="{ 'rotate-180': isGroupOpen(@js($item->getLabel())) }"
                            >
                                <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div
                            class="mason-actions-group-bricks"
                            x-show="isGroupOpen(@js($item->getLabel()))"
                        >
                            @foreach ($item->getBricks() as $brick)
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
                @else
                    <div
                        draggable="true"
                        x-on:dragstart="
                            $event.dataTransfer.setData('brick', @js($item::getId()))
                            $el.classList.add('dragging')
                        "
                        x-on:dragend="$el.classList.remove('dragging')"
                        class="mason-actions-brick"
                        x-on:open-modal.window="isLoading = false"
                        x-on:run-mason-commands.window="isLoading = false"
                        x-bind:class="{
                            'filtered': ! filterActions().includes(@js($item::getLabel())),
                        }"
                    >
                        @if (filled($item::getIcon()))
                            <x-filament::icon
                                :icon="$item::getIcon()"
                                class="h-5 w-5 shrink-0 mason-actions-brick-icon"
                            />
                        @endif

                        <span class="mason-actions-brick-label">{{ $item::getLabel() }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
