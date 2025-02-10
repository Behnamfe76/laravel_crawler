<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeleniumDriverController as SeleniumDriverControllerAlias;
use App\Http\Controllers\v1\JobController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('/dashboard')->name('dashboard.')->group(function () {
    // selenium routes
    Route::resource('/selenium-drivers', SeleniumDriverControllerAlias::class)->except(['destroy']);
    Route::post('/check-driver-status', [SeleniumDriverControllerAlias::class, 'checkDriverStatus'])->name('check-driver-status');
    Route::post('/reset-drivers', [SeleniumDriverControllerAlias::class, 'resetDrivers'])->name('reset-drivers');
    Route::post('/check-driver-alive', [SeleniumDriverControllerAlias::class, 'checkDriversAlive'])->name('check-driver-alive');
    Route::post('/check-driver-working', [SeleniumDriverControllerAlias::class, 'checkDriversWorking'])->name('check-driver-working');

    // job routes
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
})->middleware(['auth']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
