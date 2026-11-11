@extends('layouts.app')
@section('title', 'AI Writing')
@section('page', 'studio')
@section('breadcrumb', 'AI Writing')
@section('content')
    <section class="hero-panel glass-panel tool-hero"><div class="hero-copy"><span class="eyebrow">AI Studio</span><h1>Craft polished outputs across every workflow.</h1><p>Generate copy, shape product narratives, and refine your ideas with a focused writing assistant.</p></div></section>
    <section class="tool-workspace">
        <form class="glass-panel tool-form" method="POST" action="{{ route('tools.generate') }}">
            @csrf<input type="hidden" name="tool" value="writing">
            <div class="section-header compact"><div><span class="section-kicker">New draft</span><h2>Set the direction</h2></div><i class="bi bi-pencil-square tool-heading-icon"></i></div>
            <label class="form-label" for="content-type">Content type</label><select class="form-select" id="content-type" name="content_type"><option value="blog">Blog post</option><option value="website">Website content</option><option value="email">Email</option><option value="social">Social media caption</option><option value="seo">SEO description</option></select>
            <label class="form-label" for="tone">Tone</label><select class="form-select" id="tone" name="tone"><option>Clear and confident</option><option>Warm and human</option><option>Concise and direct</option><option>Playful and energetic</option></select>
            <label class="form-label" for="writing-prompt">What should the draft cover?</label><textarea class="form-control" id="writing-prompt" name="prompt" rows="6" maxlength="12000" placeholder="Share your subject, audience, key points, and any constraints..." required>{{ old('prompt') }}</textarea>
            <button class="primary-button" type="submit"><i class="bi bi-stars"></i> Generate draft</button>
        </form>
        <section class="glass-panel tool-output"><div class="section-header compact"><div><span class="section-kicker">Your output</span><h2>{{ isset($generation) ? 'Saved draft' : 'Your result appears here' }}</h2></div>@isset($generation)<button class="icon-button" type="button" data-copy-output aria-label="Copy result"><i class="bi bi-clipboard"></i></button>@endisset</div>
            @isset($generation)<div class="output-content" data-output>{{ $generation->messages->last()?->content }}</div>@else<div class="empty-state"><span class="tool-icon"><i class="bi bi-pencil-square"></i></span><p>Your generated writing will appear here and be saved in your conversation history.</p></div>@endisset
        </section>
    </section>
@endsection
