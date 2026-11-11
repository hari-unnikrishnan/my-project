@extends('layouts.app')
@section('title', 'Data Analyzer')
@section('page', 'analyzer')
@section('breadcrumb', 'Data Analyzer')
@section('content')
    <section class="hero-panel glass-panel"><div class="hero-copy"><span class="eyebrow">Insight studio</span><h1>Find the signal in your data.</h1><p>Share a dataset and a specific question. Aether will summarize patterns and call out assumptions.</p></div></section>
    <section class="tool-workspace">
        <form class="glass-panel tool-form" method="POST" action="{{ route('tools.generate') }}">
            @csrf<input type="hidden" name="tool" value="analyzer">
            <div class="section-header compact"><div><span class="section-kicker">New analysis</span><h2>Bring your data</h2></div><i class="bi bi-bar-chart-line tool-heading-icon"></i></div>
            <label class="form-label" for="analysis-data">Dataset</label><textarea class="form-control code-input" id="analysis-data" name="dataset" rows="8" maxlength="20000" placeholder="Paste a small CSV, table, or structured text dataset..." required>{{ old('dataset') }}</textarea>
            <label class="form-label" for="analysis-prompt">What would you like to understand?</label><textarea class="form-control" id="analysis-prompt" name="prompt" rows="4" maxlength="12000" placeholder="Identify the main trend and any unusual values." required>{{ old('prompt') }}</textarea>
            <button class="primary-button" type="submit"><i class="bi bi-stars"></i> Analyze data</button>
        </form>
        <section class="glass-panel tool-output"><div class="section-header compact"><div><span class="section-kicker">Analysis</span><h2>{{ isset($generation) ? 'Saved insights' : 'Your insights appear here' }}</h2></div>@isset($generation)<button class="icon-button" type="button" data-copy-output aria-label="Copy analysis"><i class="bi bi-clipboard"></i></button>@endisset</div>
            @isset($generation)<div class="output-content" data-output>{{ $generation->messages->last()?->content }}</div>@else<div class="empty-state"><span class="tool-icon"><i class="bi bi-graph-up-arrow"></i></span><p>Your analysis will be saved in conversation history.</p></div>@endisset
        </section>
    </section>
@endsection
