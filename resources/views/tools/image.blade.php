@extends('layouts.app')
@section('title', 'Image Generator')
@section('page', 'image')
@section('breadcrumb', 'Image Generator')
@section('content')
    <section class="hero-panel glass-panel"><div class="hero-copy"><span class="eyebrow">Visual studio</span><h1>Bring your next idea into view.</h1><p>Describe a visual direction and shape a concept for your product, campaign, or brand.</p></div></section>
    <section class="tool-workspace">
        <form class="glass-panel tool-form" method="POST" action="{{ route('tools.generate') }}">
            @csrf<input type="hidden" name="tool" value="image">
            <div class="section-header compact"><div><span class="section-kicker">Image brief</span><h2>Describe the image</h2></div><i class="bi bi-image tool-heading-icon"></i></div>
            <label class="form-label" for="image-prompt">Prompt</label><textarea class="form-control" id="image-prompt" name="prompt" rows="5" maxlength="4000" placeholder="A cinematic hero image for a modern AI workspace, midnight blue and violet light..." required>{{ old('prompt') }}</textarea>
            <label class="form-label" for="image-style">Visual style</label><select class="form-select" id="image-style" name="style"><option>Editorial</option><option>Photorealistic</option><option>Illustration</option><option>3D render</option></select>
            <label class="form-label" for="image-ratio">Aspect ratio</label><select class="form-select" id="image-ratio" name="aspect_ratio"><option value="1024x1024">Square · 1:1</option><option value="1536x1024">Landscape · 3:2</option><option value="1024x1536">Portrait · 2:3</option></select>
            <button class="primary-button" type="submit"><i class="bi bi-stars"></i> Create image</button>
        </form>
        <section class="glass-panel tool-output"><div class="section-header compact"><div><span class="section-kicker">Canvas</span><h2>{{ isset($generation) ? 'Generated image' : 'Your image will appear here' }}</h2></div></div>
            @isset($generation)<div class="generated-image"><img src="{{ route('chat.image', [$generation, $generation->messages->last()]) }}" alt="AI-generated image"></div>@else<div class="empty-state image-empty"><span class="tool-icon"><i class="bi bi-stars"></i></span><p>Images generated for your account will be saved in this workspace.</p></div>@endisset
        </section>
    </section>
@endsection
