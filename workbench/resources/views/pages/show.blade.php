<x-layouts.app :title="$page->title">
    {{-- The third argument reaches every brick's toHtml() as $data. --}}
    @mason(
        content: $page->content,
        bricks: \Workbench\App\Mason\BrickCollection::make(),
        data: ['record' => $page]
    )
</x-layouts.app>
