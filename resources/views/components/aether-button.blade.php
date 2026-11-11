@props(['href' => null, 'variant' => 'primary', 'type' => 'button'])
@if ($href)
    <a {{ $attributes->merge(['class' => $variant . '-button']) }} href="{{ $href }}">{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => $variant . '-button', 'type' => $type]) }}>{{ $slot }}</button>
@endif
