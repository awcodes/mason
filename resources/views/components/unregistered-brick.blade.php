@props([
    'label' => null,
])

<div class="flex items-center gap-3 not-prose p-4 bg-gray-100 dark:bg-gray-800">
    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-6 w-6 text-danger-600 dark:text-danger-400" />
    <p>{{ trans('mason::mason.unregistered_brick', ['label' => $label]) }}</p>
</div>
