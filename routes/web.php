<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use App\Http\Controllers\Pharmacist\DashboardController as PharmacistDashboardController;
use App\Http\Controllers\Pharmacist\PatientDetailController as PharmacistPatientDetailController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    // Special admin redirect route
    Route::get('admin-redirect', function () {
        return Inertia::render('auth/AdminRedirect');
    })->name('admin.redirect')->middleware('role:' . User::ROLE_ADMIN);

    // General dashboard route that redirects to appropriate dashboard based on user role
    Route::get('dashboard', function () {
        $user = Auth::user();

        if ($user->hasRole(User::ROLE_ADMIN)) {
            return redirect()->route('admin.redirect');
        } elseif ($user->hasRole(User::ROLE_DOCTOR)) {
            return redirect()->route('dashboard.doctor');
        } elseif ($user->hasRole(User::ROLE_PHARMACIST)) {
            return redirect()->route('dashboard.pharmacist');
        }

        // Fallback for users without specific roles
        return redirect()->route('login');
    })->name('dashboard');

    Route::group(['middleware' => 'role:' . User::ROLE_DOCTOR], function () {
        Route::get('dashboard/doctor', [DoctorDashboardController::class, 'index'])->name('dashboard.doctor');
        Route::get('doctor/patients/search', [DoctorDashboardController::class, 'searchPatients'])->name('doctor.patients.search');
        Route::get('doctor/medicines/search', [DoctorDashboardController::class, 'searchMedicines'])->name('doctor.medicines.search');
        Route::get('doctor/medicines/{id}', [DoctorDashboardController::class, 'getMedicine'])->name('doctor.medicines.show');
        Route::post('doctor/prescription/submit', [DoctorDashboardController::class, 'submitPrescription'])->name('doctor.prescription.submit');
    });

    Route::group(['middleware' => 'role:' . User::ROLE_PHARMACIST], function () {
        // Dashboard routes
        Route::get('dashboard/pharmacist', [PharmacistDashboardController::class, 'index'])->name('dashboard.pharmacist');

        // Patient detail routes
        Route::get('dashboard/pharmacist/patient/{id}', [PharmacistPatientDetailController::class, 'show'])->name('dashboard.pharmacist.patient');
        Route::put('dashboard/pharmacist/patient/{id}/status', [PharmacistPatientDetailController::class, 'updatePrescriptionStatus'])->name('pharmacist.patient.status');
        Route::post('dashboard/pharmacist/patient/{id}/payment', [PharmacistPatientDetailController::class, 'processPayment'])->name('pharmacist.patient.payment');
        Route::post('dashboard/pharmacist/patient/{id}/invoice', [PharmacistPatientDetailController::class, 'getInvoiceData'])->name('pharmacist.patient.invoice');
    });
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
