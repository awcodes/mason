@php
    use Filament\Support\Facades\FilamentView;

    $id = $getId();
    $key = $getKey();
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
    $bricks = $getBricks();
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
            placeholder: @js($getPlaceholder()),
            deleteBrickButtonIconHtml: @js(\Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::Trash, alias: 'mason::delete-brick-button')->toHtml()),
            editBrickButtonIconHtml: @js(\Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::PencilSquare, alias: 'mason::edit-brick-button')->toHtml()),
            insertAboveBrickButtonIconHtml: @js(\Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::BarsArrowUp, alias: 'mason::insert-brick-button')->toHtml()),
            insertBelowBrickButtonIconHtml: @js(\Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::BarsArrowDown, alias: 'mason::insert-brick-button')->toHtml()),
            moveBrickUpButtonIconHtml: @js(\Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::ArrowUp, alias: 'mason::move-brick-up-button')->toHtml()),
            moveBrickDownButtonIconHtml: @js(\Filament\Support\generate_icon_html(\Filament\Support\Icons\Heroicon::ArrowDown, alias: 'mason::move-brick-down-button')->toHtml()),
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
        x-on:keydown.escape.window="fullscreen = false"
    >
        <x-filament::input.wrapper
            :valid="! $errors->has($statePath)"
            :attributes="
                \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
                    ->class([
                        'mason-input-wrapper',

                    ])
            "
        >
            <div
                @class([
                    'flex flex-1',
                    'flex-row-reverse' => $getSidebarPosition() === \Awcodes\Mason\Enums\SidebarPosition::Start,
                ])
            >
                <div class="mason-editor-wrapper">
                    <div
                        class="mason-editor"
                        x-ref="editor"
                        wire:ignore
                    ></div>
                </div>

                @if (! $isDisabled && filled($bricks))
                    <div wire:key="sidebar-{{ hash('sha256', json_encode($bricks)) }}">
                        <x-mason::sidebar :bricks="$bricks" />
                    </div>
                @endif
            </div>
        </x-filament::input.wrapper>
    </div>
</x-dynamic-component>
