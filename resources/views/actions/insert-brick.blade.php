@props([
    'bricks' => []
])

<div
    class="mason-actions"
    wire:ignore
    x-data="{
        actions: @js(array_keys($bricks)),
        search: '',
        filterActions: function() {
            return this.actions.filter(
                name => name.toLowerCase().includes(this.search.toLowerCase())
            );
        }
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
        @if ($bricks)
            @foreach ($bricks as $brick)
                <div
                    class="mason-actions-brick"
                    x-bind:class="{
                        'filtered': ! filterActions().includes(@js($brick->getLabel())),
                    }"
                >
                    {{ $brick }}
                </div>
            @endforeach
        @endif
    </div>
</div>
