@extends('layouts.app')
@section('title', 'AI Automation')
@section('page', 'automation')
@section('breadcrumb', 'Automation')
@section('content')
    <section class="hero-panel glass-panel"><div class="hero-copy"><span class="eyebrow">AI automation</span><h1>Give repetitive work a smarter path.</h1><p>Describe a workflow and map the steps you want to streamline.</p></div></section>
    <section class="tool-workspace">
        <form class="glass-panel tool-form" method="POST" action="{{ route('tools.generate') }}">
            @csrf<input type="hidden" name="tool" value="automation">
            <div class="section-header compact"><div><span class="section-kicker">Workflow design</span><h2>Start with a trigger</h2></div><i class="bi bi-diagram-3 tool-heading-icon"></i></div>
            <label class="form-label" for="workflow-name">Workflow name</label><input class="form-control" id="workflow-name" name="workflow_name" value="{{ old('workflow_name') }}" maxlength="120" placeholder="e.g. Weekly product update">
            <label class="form-label" for="workflow-trigger">When should it start?</label><select class="form-select" id="workflow-trigger" name="trigger"><option>On a schedule</option><option>When I run it manually</option><option>When new content is added</option></select>
            <label class="form-label" for="workflow-goal">What should happen?</label><textarea class="form-control" id="workflow-goal" name="prompt" rows="5" maxlength="12000" placeholder="Describe the steps and the outcome you want..." required>{{ old('prompt') }}</textarea>
            <button class="primary-button" type="submit"><i class="bi bi-stars"></i> Design workflow</button>
        </form>
        <section class="glass-panel tool-output"><span class="section-kicker">Workflow outline</span><h2>{{ isset($generation) ? 'Your saved workflow design' : 'Nothing running yet' }}</h2>
            @isset($generation)<div class="output-content" data-output>{{ $generation->messages->last()?->content }}</div>@else<div class="empty-state"><span class="tool-icon"><i class="bi bi-diagram-3"></i></span><p>Describe a workflow to receive a plan. Running automations can be managed when connected services are configured.</p></div>@endisset
        </section>
    </section>
@endsection
