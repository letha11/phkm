<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use App\Http\Controllers\Pharmacist\DashboardController as PharmacistDashboardController;
use App\Http\Controllers\Pharmacist\PatientDetailController as PharmacistPatientDetailController;

Route::get('/', function () {
    return redirect()->route('login');
});

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
        // Dashboard routes
        Route::get('dashboard/pharmacist', [PharmacistDashboardController::class, 'index'])->name('dashboard.pharmacist');
        
        // Patient detail routes
        Route::get('dashboard/pharmacist/patient/{id}', [PharmacistPatientDetailController::class, 'show'])->name('dashboard.pharmacist.patient');
        Route::put('dashboard/pharmacist/patient/{id}/status', [PharmacistPatientDetailController::class, 'updateStatus'])->name('pharmacist.patient.status');
        Route::post('dashboard/pharmacist/patient/{id}/payment', [PharmacistPatientDetailController::class, 'processPayment'])->name('pharmacist.patient.payment');
        Route::post('dashboard/pharmacist/patient/{id}/invoice', [PharmacistPatientDetailController::class, 'getInvoiceData'])->name('pharmacist.patient.invoice');
    });
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
