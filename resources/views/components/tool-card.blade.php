@props(['icon', 'title', 'description', 'accent', 'href'])
<article class="tool-card tool-card--{{ $accent }}">
    <div class="tool-icon"><i class="bi {{ $icon }}"></i></div>
    <h3>{{ $title }}</h3>
    <p>{{ $description }}</p>
    <a class="mini-action" href="{{ $href }}">Open tool <i class="bi bi-arrow-up-right"></i></a>
</article>
