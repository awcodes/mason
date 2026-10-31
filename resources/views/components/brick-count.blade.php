@props([
    'brick',
])

{{-- How often a brick with getMaxInstances() is used, e.g. 1/1 --}}
@if (($max = $brick::getMaxInstances()) !== null)
    <span class="mason-brick-count">
        <span
            aria-hidden="true"
            x-text="`${brickCount(@js($brick::getId()))}/{{ $max }}`"
        ></span>
        <span
            class="sr-only"
            x-text="@js(__('mason::mason.max_instances.used')).replace(':count', brickCount(@js($brick::getId()))).replace(':max', {{ $max }})"
        ></span>
    </span>
@endif
