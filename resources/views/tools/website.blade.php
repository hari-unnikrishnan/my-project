@extends('layouts.app')
@section('title', 'Website Builder')
@section('page', 'website')
@section('breadcrumb', 'Website Builder')
@section('content')
    <section class="hero-panel glass-panel"><div class="hero-copy"><span class="eyebrow">Web experience builder</span><h1>Go from a brief to a clear site direction.</h1><p>Shape the story, page structure, and conversion flow for your next launch.</p></div></section>
    <section class="tool-workspace">
        <form class="glass-panel tool-form" method="POST" action="{{ route('tools.generate') }}">
            @csrf<input type="hidden" name="tool" value="website">
            <div class="section-header compact"><div><span class="section-kicker">Project brief</span><h2>Tell us about your website</h2></div><i class="bi bi-window-fullscreen tool-heading-icon"></i></div>
            <label class="form-label" for="site-name">Project name</label><input class="form-control" id="site-name" name="project_name" value="{{ old('project_name') }}" maxlength="120" placeholder="e.g. Northstar Analytics">
            <label class="form-label" for="site-audience">Audience and goal</label><textarea class="form-control" id="site-audience" name="prompt" rows="5" maxlength="12000" placeholder="Describe the audience, value proposition, and main action..." required>{{ old('prompt') }}</textarea>
            <label class="form-label" for="site-pages">Pages to include</label><input class="form-control" id="site-pages" name="pages" value="{{ old('pages') }}" maxlength="500" placeholder="Home, Product, Pricing, About">
            <label class="form-label" for="site-tone">Brand tone</label><select class="form-select" id="site-tone" name="tone"><option>Confident and clear</option><option>Warm and human</option><option>Bold and energetic</option></select>
            <button class="primary-button" type="submit"><i class="bi bi-stars"></i> Create site outline</button>
        </form>
        <section class="glass-panel tool-output"><div class="section-header compact"><div><span class="section-kicker">Site outline</span><h2>{{ isset($generation) ? 'Saved site direction' : 'Start with your brief' }}</h2></div>@isset($generation)<button class="icon-button" type="button" data-copy-output aria-label="Copy outline"><i class="bi bi-clipboard"></i></button>@endisset</div>
            @isset($generation)<div class="output-content" data-output>{{ $generation->messages->last()?->content }}</div>@else<div class="empty-state"><span class="tool-icon"><i class="bi bi-layout-text-window-reverse"></i></span><p>Your page structure, messaging direction, and content outline will appear here.</p></div>@endisset
        </section>
    </section>
@endsection
