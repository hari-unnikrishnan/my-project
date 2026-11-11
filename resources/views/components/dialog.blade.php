@props(['id', 'title'])
<div class="modal" id="{{ $id }}" data-modal aria-hidden="true">
    <div class="modal-card glass-panel" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
        <div class="modal-header">
            <h3 id="{{ $id }}-title">{{ $title }}</h3>
            <button type="button" class="icon-button" data-modal-close aria-label="Close dialog"><i class="bi bi-x-lg"></i></button>
        </div>
        {{ $slot }}
    </div>
</div>
