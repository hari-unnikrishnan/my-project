@extends('layouts.landing')

@section('title', 'One workspace for your next big idea')

@section('content')
<header class="landing-nav">
    <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">A</span><span class="brand-copy"><strong>Aether AI</strong><small>workspace</small></span></a>
    <nav aria-label="Main navigation"><a href="#workflows">Workflows</a><a href="#why-aether">Why Aether</a></nav>
    <div class="landing-actions">
        <a class="secondary-button" href="{{ route('login') }}">Sign in</a>
        <a class="primary-button" href="{{ route('register') }}">Get started <i class="bi bi-arrow-up-right"></i></a>
    </div>
</header>
<main>
    <section class="landing-hero">
        <div class="landing-orbit landing-orbit-one"></div><div class="landing-orbit landing-orbit-two"></div>
        <div class="landing-copy">
            <span class="eyebrow"><i class="bi bi-stars"></i> A calmer way to work with AI</span>
            <h1>Your ideas,<br><span>in motion.</span></h1>
            <p>Think, create, and ship from one focused AI workspace. Keep your conversations, tools, and next steps together.</p>
            <div class="landing-actions"><a class="primary-button" href="{{ route('register') }}">Start creating <i class="bi bi-arrow-right"></i></a><a class="secondary-button" href="{{ route('login') }}">Explore the workspace</a></div>
            <div class="landing-trust"><i class="bi bi-shield-check"></i> Your workspace. Your context. Your control.</div>
        </div>
        <div class="landing-preview glass-panel">
            <div class="preview-top"><span class="preview-dots"><i></i><i></i><i></i></span><span>Aether workspace</span><span class="status-pill success">Ready</span></div>
            <div class="preview-workspace"><div class="preview-rail"><span class="brand-mark">A</span><i class="bi bi-grid-1x2-fill"></i><i class="bi bi-chat-dots"></i><i class="bi bi-stars"></i><i class="bi bi-code-square"></i></div><div class="preview-content"><span class="section-kicker">Your AI workspace</span><h2>Make room for<br>your best work.</h2><div class="preview-prompt"><i class="bi bi-stars"></i><span>What would you like to create today?</span><i class="bi bi-arrow-up-circle-fill"></i></div><div class="preview-tools"><article><i class="bi bi-pencil-square"></i><strong>Writing</strong><small>Find the right words</small></article><article><i class="bi bi-code-square"></i><strong>Building</strong><small>Make ideas real</small></article><article><i class="bi bi-image"></i><strong>Creating</strong><small>Explore new visuals</small></article></div></div></div>
        </div>
    </section>
    <section class="landing-section" id="workflows"><div class="landing-section-heading"><span class="section-kicker">One connected workspace</span><h2>Move from a spark to a finished idea.</h2><p>Bring your everyday AI work into a space designed for focus and follow-through.</p></div><div class="landing-feature-grid">
        <article class="tool-card tool-card--violet"><div class="tool-icon"><i class="bi bi-chat-dots"></i></div><h3>Think with context</h3><p>Keep meaningful conversations organized and easy to continue.</p></article>
        <article class="tool-card tool-card--blue"><div class="tool-icon"><i class="bi bi-pencil-square"></i></div><h3>Create with clarity</h3><p>Draft content that sounds considered, confident, and unmistakably yours.</p></article>
        <article class="tool-card tool-card--teal"><div class="tool-icon"><i class="bi bi-code-square"></i></div><h3>Build with momentum</h3><p>Work through code, concepts, and technical questions with one assistant.</p></article>
        <article class="tool-card tool-card--amber"><div class="tool-icon"><i class="bi bi-layers"></i></div><h3>Keep work together</h3><p>Return to your projects without losing the thread of what matters.</p></article>
    </div></section>
    <section class="landing-cta glass-panel" id="why-aether"><div><span class="eyebrow">Designed for the way you think</span><h2>Less switching.<br>More meaningful progress.</h2><p>Aether AI brings your creative and technical workflows into one thoughtful space.</p></div><a class="primary-button" href="{{ route('register') }}">Find your flow <i class="bi bi-arrow-right"></i></a></section>
</main>
<footer class="landing-footer"><a class="brand" href="{{ route('landing') }}"><span class="brand-mark">A</span><span class="brand-copy"><strong>Aether AI</strong><small>workspace</small></span></a><span>Thoughtful tools for ambitious work.</span><a href="{{ route('login') }}">Sign in <i class="bi bi-arrow-up-right"></i></a></footer>
@endsection
