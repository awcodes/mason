@php
    use Filament\Support\Facades\FilamentView;
    use Filament\Support\Icons\Heroicon;
    use function Filament\Support\generate_icon_html;

    $id = $getId();
    $key = $getKey();
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
    $bricks = $getBricks();
    $defaultColorMode = $getDefaultColorMode();
    $hasColorModeToggle = $hasColorModeToggle();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        @if (FilamentView::hasSpaMode())
            {{-- format-ignore-start --}}x-load="visible || event (x-modal-opened)"{{-- format-ignore-end --}}
        @else
            x-load
        @endif
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc("mason", "awcodes/mason") }}"
        x-data="masonComponent({
                    key: @js($key),
                    livewireId: @js($this->getId()),
                    state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')", isOptimisticallyLive: false) }},
                    statePath: @js($statePath),
                    placeholder: @js($getPlaceholder()),
                    disabled: @js($isDisabled),
                    dblClickToEdit: @js($shouldDblClickToEdit()),
                    bricks: @js(array_map(fn ($brick) => is_string($brick) ? $brick : get_class($brick), $bricks)),
                    previewLayout: @js($getPreviewLayout()),
                    defaultColorMode: @js($defaultColorMode),
                    hasColorModeToggle: @js($hasColorModeToggle),
                })"
        id="{{ "mason-wrapper-" . $statePath }}"
        class="mason-wrapper"
        tabindex="-1"
        x-bind:class="{
            'fullscreen': fullscreen,
            'display-mobile': viewport === 'mobile',
            'display-tablet': viewport === 'tablet',
            'display-desktop': viewport === 'desktop',
        }"
        x-on:keydown.escape.window="fullscreen = false"
        x-on:click.away="deselectAllBlocks()"
    >
        @if (! $isDisabled)
            <div class="mason-topbar">
                <x-mason::controls
                    :has-color-mode-toggle="$hasColorModeToggle"
                />
            </div>
        @endif

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
                    "flex flex-1",
                    "flex-row-reverse" =>
                        $getSidebarPosition() === \Awcodes\Mason\Enums\SidebarPosition::Start,
                ])
            >
                <div
                    class="mason-editor-wrapper"
                    wire:ignore
                    {{
                        \Filament\Support\prepare_inherited_attributes($getExtraInputAttributeBag())->class([
                            "mason-input-wrapper",
                        ])
                    }}
                >
                    <iframe
                        x-ref="previewIframe"
                        name="{{ "mason-preview-iframe-" . $statePath }}"
                        class="mason-iframe"
                    ></iframe>
                </div>

                @if (! $isDisabled && filled($bricks))
                    <x-mason::sidebar
                        :bricks="$bricks"
                        :has-grid-actions="$hasGridActions()"
                        :has-color-mode-toggle="$hasColorModeToggle"
                        wire:key="sidebar-{{ hash('sha256', json_encode($bricks)) }}"
                    />
                @endif
            </div>
        </x-filament::input.wrapper>

        @if (! $isDisabled && filled($bricks))
            <x-mason::brick-picker-modal :bricks="$bricks" />
        @endif
    </div>
</x-dynamic-component>
