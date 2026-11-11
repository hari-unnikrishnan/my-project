@props(['icon', 'label', 'value', 'detail'])
<article class="stat-card glass-panel">
    <div class="stat-topline"><i class="bi {{ $icon }}"></i><span>{{ $label }}</span></div>
    <div class="stat-number">{{ $value }}</div>
    <div class="stat-detail">{{ $detail }}</div>
</article>
