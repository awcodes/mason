<div style="padding: 1.5rem">
    <div
        style="
            display: grid;
            grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr));
            gap: 1.5rem;
        "
    >
        @foreach ($children as $child)
            <div style="min-width: 0">{{ $child }}</div>
        @endforeach
    </div>
    {{ $children->dropZone() }}
</div>
