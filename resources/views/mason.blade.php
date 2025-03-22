@php
    use Filament\Support\Facades\FilamentView;

    $id = $getId();
    $key = $getKey();
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
    $actions = collect($getActions())->filter(fn ($action) => $action->getName() !== 'insertBrick')->toArray();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        @if (FilamentView::hasSpaMode())
            {{-- format-ignore-start --}}x-load="visible || event (x-modal-opened)"{{-- format-ignore-end --}}
        @else
            x-load
        @endif
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('mason', 'awcodes/mason') }}"
        x-data="masonComponent({
            key: @js($key),
            livewireId: @js($this->getId()),
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')", isOptimisticallyLive: false) }},
            statePath: @js($statePath),
            placeholder: @js($getPlaceholder())
        })"
        id="{{ 'mason-wrapper-' . $statePath }}"
        class="mason-wrapper"
        x-bind:class="{
            'fullscreen': fullscreen,
            'is-focused': isFocused,
            'display-mobile': viewport === 'mobile',
            'display-tablet': viewport === 'tablet',
            'display-desktop': viewport === 'desktop'
        }"
        x-on:click.away="blurEditor()"
        x-on:focus-editor.window="focusEditor($event)"
        x-on:dragged-brick.stop="handleBrickDrop($event)"
        x-on:handle-brick-insert.window="handleBrickInsert($event)"
        x-on:keydown.escape.window="fullscreen = false"
    >
        <x-filament::input.wrapper
            :valid="! $errors->has($statePath)"
            :attributes="
                \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
                    ->class([
                        'mason-input-wrapper',
                        'sidebar-start' => $getSidebarPosition() === \Awcodes\Mason\Enums\SidebarPosition::Start,
                    ])
            "
        >
            <div class="mason-editor-wrapper">
                <div
                    class="mason-editor"
                    x-ref="editor"
                    wire:ignore
                ></div>
            </div>

            @if (! $isDisabled && filled($actions))
                <div wire:key="sidebar-{{ hash('sha256', json_encode($actions)) }}">
                    <x-mason::sidebar :actions="$actions" />
                </div>
            @endif
        </x-filament::input.wrapper>
    </div>
</x-dynamic-component>
