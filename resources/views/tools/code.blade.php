@extends('layouts.app')
@section('title', 'Code Assistant')
@section('page', 'code')
@section('breadcrumb', 'Code Assistant')
@section('content')
    <section class="hero-panel glass-panel"><div class="hero-copy"><span class="eyebrow">Developer workspace</span><h1>Think through code, faster.</h1><p>Generate, debug, and explain code with a focused assistant for your stack.</p></div></section>
    <section class="tool-workspace">
        <form class="glass-panel tool-form" method="POST" action="{{ route('tools.generate') }}">
            @csrf<input type="hidden" name="tool" value="code">
            <div class="section-header compact"><div><span class="section-kicker">New request</span><h2>Tell us what you’re building</h2></div><i class="bi bi-code-slash tool-heading-icon"></i></div>
            <label class="form-label" for="code-task">Task</label><select class="form-select" id="code-task" name="task"><option value="generate">Generate code</option><option value="debug">Debug code</option><option value="explain">Explain code</option></select>
            <label class="form-label" for="language">Language</label><select class="form-select" id="language" name="language"><option>PHP</option><option>HTML</option><option>CSS</option><option>JavaScript</option><option>Python</option><option>SQL</option></select>
            <label class="form-label" for="code-prompt">Instructions</label><textarea class="form-control" id="code-prompt" name="prompt" rows="5" maxlength="12000" placeholder="Describe the problem, expected behavior, and relevant constraints..." required>{{ old('prompt') }}</textarea>
            <label class="form-label" for="code-input">Code context <span class="muted-label">Optional</span></label><textarea class="form-control code-input" id="code-input" name="code" rows="8" maxlength="20000" placeholder="// Paste code to review or debug">{{ old('code') }}</textarea>
            <button class="primary-button" type="submit"><i class="bi bi-stars"></i> Generate solution</button>
        </form>
        <section class="glass-panel tool-output"><div class="section-header compact"><div><span class="section-kicker">Assistant output</span><h2>{{ isset($generation) ? 'Saved solution' : 'Your result appears here' }}</h2></div>@isset($generation)<button class="icon-button" type="button" data-copy-output aria-label="Copy result"><i class="bi bi-clipboard"></i></button>@endisset</div>
            @isset($generation)<pre class="output-content" data-output>{{ $generation->messages->last()?->content }}</pre>@else<div class="empty-state"><span class="tool-icon"><i class="bi bi-braces"></i></span><p>Describe a coding task to get started. Your generated code and explanations will appear in this space.</p></div>@endisset
        </section>
    </section>
@endsection
