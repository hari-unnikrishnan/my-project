<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Auth::user()->settings()->firstOrCreate();

        return view('settings', compact('settings'));
    }

    public function account()
    {
        $settings = Auth::user()->settings()->firstOrCreate();

        return view('account-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', Rule::in(['dark', 'light'])],
            'language' => ['required', Rule::in(['en'])],
            'timezone' => ['required', 'timezone'],
            'email_notifications' => ['sometimes', 'boolean'],
            'marketing_emails' => ['sometimes', 'boolean'],
        ]);

        $request->user()->settings()->updateOrCreate([], [
            'theme' => $validated['theme'],
            'language' => $validated['language'],
            'timezone' => $validated['timezone'],
            'email_notifications' => $request->boolean('email_notifications'),
            'marketing_emails' => $request->boolean('marketing_emails'),
        ]);

        return redirect()->route('settings.index')->with('status', 'Your preferences were saved.');
    }

    public function updateTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme' => ['required', Rule::in(['dark', 'light'])],
        ]);

        $request->user()->settings()->updateOrCreate([], ['theme' => $validated['theme']]);

        return response()->json(['theme' => $validated['theme']]);
    }
}
