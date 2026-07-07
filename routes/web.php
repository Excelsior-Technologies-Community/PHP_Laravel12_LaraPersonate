<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('admin')
        ->name('users');

    Route::delete('/users/{id}', [UserController::class, 'destroy'])
        ->middleware('admin')
        ->name('users.destroy');

    Route::post('/impersonate/{id}', [ImpersonationController::class, 'impersonate'])
        ->middleware('admin')
        ->name('admin.impersonate.start');

    Route::post('/impersonate-leave', [ImpersonationController::class, 'leave'])
        ->name('admin.impersonate.stop');

    Route::get('/force-logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('force.logout');
});

require __DIR__ . '/auth.php';