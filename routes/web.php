<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ToolsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'landing'])->name('landing');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('/history', [ChatController::class, 'history'])->name('chat.history');
    Route::post('/chat', [ChatController::class, 'store'])->middleware('throttle:10,1')->name('chat.store');
    Route::post('/chat/{conversation}/messages', [ChatController::class, 'storeMessage'])->middleware('throttle:10,1')->name('chat.messages.store');
    Route::delete('/chat/{conversation}', [ChatController::class, 'destroy'])->name('chat.destroy');
    Route::post('/chat/{conversation}/messages/{message}/regenerate', [ChatController::class, 'regenerate'])->middleware('throttle:10,1')->name('chat.regenerate');
    Route::get('/chat/{conversation}/messages/{message}/image', [ChatController::class, 'image'])->name('chat.image');

    Route::get('/writing', [ToolsController::class, 'writing'])->name('tools.writing');
    Route::get('/code', [ToolsController::class, 'code'])->name('tools.code');
    Route::get('/image', [ToolsController::class, 'image'])->name('tools.image');
    Route::get('/website', [ToolsController::class, 'website'])->name('tools.website');
    Route::get('/documents', [ToolsController::class, 'documents'])->name('tools.documents');
    Route::get('/automation', [ToolsController::class, 'automation'])->name('tools.automation');
    Route::get('/analyzer', [ToolsController::class, 'analyzer'])->name('tools.analyzer');
    Route::post('/studio/generate', [ToolsController::class, 'generate'])->middleware('throttle:10,1')->name('tools.generate');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/account', [SettingsController::class, 'account'])->name('settings.account');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::patch('/settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::patch('/tools/{tool}', [AdminController::class, 'updateTool'])->name('tools.update');
    });
});

require __DIR__.'/auth.php';