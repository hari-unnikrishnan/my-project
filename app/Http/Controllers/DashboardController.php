<?php

namespace App\Http\Controllers;

use App\Models\AiTool;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function landing()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('landing');
    }

    public function index()
    {
        $user = Auth::user();

        $stats = [
            'conversations' => $user->conversations()->count(),
            'requests' => $user->apiUsages()->sum('requests_used'),
            'tools' => AiTool::where('is_active', true)->count(),
        ];

        $recentConversations = $user->conversations()
            ->withCount('messages')
            ->latest('updated_at')
            ->limit(5)
            ->get();
        $recentUsage = $user->apiUsages()->latest()->limit(8)->get();
        $routes = [
            'content-writer' => 'tools.writing',
            'code-generator' => 'tools.code',
            'image-generator' => 'tools.image',
            'website-builder' => 'tools.website',
            'email-assistant' => 'tools.writing',
            'seo-assistant' => 'tools.writing',
            'document-creator' => 'tools.documents',
            'data-analyzer' => 'tools.analyzer',
        ];
        $accents = ['purple' => 'violet', 'red' => 'pink'];
        $availableTools = AiTool::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(fn (AiTool $tool) => [
                'name' => $tool->name,
                'description' => $tool->description,
                'icon' => $tool->icon ?: 'bi-stars',
                'accent' => $accents[$tool->accent_class] ?? $tool->accent_class ?? 'violet',
                'href' => route($routes[$tool->slug] ?? 'tools.writing'),
            ]);

        return view('dashboard', compact('stats', 'recentConversations', 'recentUsage', 'availableTools'));
    }
}
