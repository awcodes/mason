<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div
        {{
            \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
                ->class([
                    'mason-entry',
                ])
        }}
    >
        <div class="mason">
            {!! mason($getState())->bricks($getBricks())->toHtml() !!}
        </div>
    </div>
</x-dynamic-component>
