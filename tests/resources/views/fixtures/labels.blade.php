@foreach ($blocks as $block)
    <div data-label="{{ $block['label'] }}">{!! $block['html'] !!}</div>
@endforeach
