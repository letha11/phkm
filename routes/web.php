<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::group(['middleware' => 'role:' . User::ROLE_ADMIN], function () {
        Route::get('dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');
    });
    
    // Route::middleware('role:' . User::ROLE_ADMIN)->group(function () {
    //     Route::get('dashboard', function () {
    //         return Inertia::render('Dashboard');
    //     })->name('dashboard');
    // });

    Route::group(['middleware' => 'role:' . User::ROLE_DOCTOR], function () {
        Route::get('dashboard/doctor', function () {
            return Inertia::render('doctor/Dashboard');
        })->name('dashboard.doctor');
    });

    Route::group(['middleware' => 'role:' . User::ROLE_PHARMACIST], function () {
        Route::get('dashboard/pharmacist', function () {
            return Inertia::render('pharmacist/Dashboard');
        })->name('dashboard.pharmacist');
    });
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
