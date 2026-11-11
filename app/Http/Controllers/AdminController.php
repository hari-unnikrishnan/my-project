<?php

namespace App\Http\Controllers;

use App\Models\ApiUsage;
use App\Models\AiTool;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $users = User::latest()->limit(8)->get();
        $usage = ApiUsage::with('user')->latest()->limit(10)->get();
        $tools = AiTool::orderBy('name')->get();
        $stats = [
            'users' => User::count(),
            'admins' => User::where('is_admin', true)->orWhere('role', 'admin')->count(),
            'requests' => ApiUsage::sum('requests_used'),
            'tools' => AiTool::where('is_active', true)->count(),
        ];

        return view('admin', compact('users', 'usage', 'tools', 'stats'));
    }

    public function users(): View
    {
        $users = User::latest()->paginate(15);

        return view('admin.users', compact('users'));
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['user', 'admin'])],
        ]);

        if ($user->is($request->user())) {
            return back()->withErrors(['role' => 'You cannot change your own administrator role.']);
        }

        $isCurrentlyAdmin = $user->is_admin || $user->role === 'admin';
        if ($isCurrentlyAdmin && $validated['role'] === 'user') {
            $adminCount = User::where('is_admin', true)->orWhere('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->withErrors(['role' => 'The last administrator cannot be demoted.']);
            }
        }

        $user->fill([
            'role' => $validated['role'],
            'is_admin' => $validated['role'] === 'admin',
        ])->save();

        return back()->with('status', 'User role updated.');
    }

    public function updateTool(Request $request, AiTool $tool): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $tool->update(['is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'AI tool availability updated.');
    }
}
