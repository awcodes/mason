<x-layouts.app :title="$page->title">
    @mason($page->content, \Workbench\App\Mason\BrickCollection::make())
</x-layouts.app>
