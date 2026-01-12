<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MokuController;

Route::get('/', HomeController::class)->name('home');
Route::get('/about', AboutController::class)->name('about');
Route::get('/services', ServicesController::class)->name('services');
Route::get('/contact', ContactController::class)->name('contact');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Moku:Go Interface (public for now, can add auth later)
Route::prefix('moku')->group(function () {
    Route::get('/', [MokuController::class, 'index'])->name('moku.dashboard');
    Route::get('/devices', [MokuController::class, 'devices'])->name('moku.devices');
    Route::get('/device-info', [MokuController::class, 'deviceInfo'])->name('moku.device-info');
    Route::get('/execute', [MokuController::class, 'executeCommand'])->name('moku.execute');
});

require __DIR__.'/settings.php';
