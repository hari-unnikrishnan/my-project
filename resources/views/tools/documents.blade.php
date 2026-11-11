@extends('layouts.app')
@section('title', 'Document Generator')
@section('page', 'docs')
@section('breadcrumb', 'Documents')
@section('content')
    <section class="hero-panel glass-panel"><div class="hero-copy"><span class="eyebrow">Document studio</span><h1>Make a first draft feel like a final draft.</h1><p>Turn your brief into a well-structured proposal, report, or team document.</p></div></section>
    <section class="tool-workspace">
        <form class="glass-panel tool-form" method="POST" action="{{ route('tools.generate') }}">
            @csrf<input type="hidden" name="tool" value="documents">
            <div class="section-header compact"><div><span class="section-kicker">New document</span><h2>Set the direction</h2></div><i class="bi bi-file-earmark-richtext tool-heading-icon"></i></div>
            <label class="form-label" for="document-type">Document type</label><select class="form-select" id="document-type" name="document_type"><option>Project proposal</option><option>Product brief</option><option>Research report</option><option>Meeting summary</option><option>Standard operating procedure</option></select>
            <label class="form-label" for="document-title">Working title</label><input class="form-control" id="document-title" name="title" value="{{ old('title') }}" maxlength="160" placeholder="Give your document a title">
            <label class="form-label" for="document-context">Key details and context</label><textarea class="form-control" id="document-context" name="prompt" rows="6" maxlength="12000" placeholder="Audience, goals, facts to include, and any constraints..." required>{{ old('prompt') }}</textarea>
            <button class="primary-button" type="submit"><i class="bi bi-stars"></i> Draft document</button>
        </form>
        <section class="glass-panel tool-output"><div class="section-header compact"><div><span class="section-kicker">Document preview</span><h2>{{ isset($generation) ? 'Saved document' : 'Your draft appears here' }}</h2></div>@isset($generation)<button class="secondary-button" type="button" data-copy-output><i class="bi bi-clipboard"></i> Copy</button>@endisset</div>
            @isset($generation)<div class="output-content" data-output>{{ $generation->messages->last()?->content }}</div>@else<div class="empty-state"><span class="tool-icon"><i class="bi bi-file-earmark-text"></i></span><p>Your structured document will be available here after you submit a brief.</p></div>@endisset
        </section>
    </section>
@endsection
