<?php

use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('kiro-welcome');
// })->name('home');

// Public registration routes
Route::get('/', [ApplicationController::class, 'create'])->name('applications.create');
Route::post('/', [ApplicationController::class, 'store'])->name('applications.store');

// Email verification routes
Route::get('/verify/{token}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/verification/resend/{email}', [VerificationController::class, 'resend'])->name('verification.resend');
Route::view('/verification/success', 'verification.success')->name('verification.success');
Route::view('/verification/expired', 'verification.expired')->name('verification.expired');

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin routes (protected by admin middleware)
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminApplicationController::class, 'dashboard'])->name('dashboard');
    Route::post('/toggle-registration', [AdminApplicationController::class, 'toggleRegistration'])->name('toggleRegistration');
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/export', [AdminApplicationController::class, 'export'])->name('applications.export');
    Route::get('/applications/{id}', [AdminApplicationController::class, 'show'])->name('applications.show');
    Route::get('/applications/{id}/edit', [AdminApplicationController::class, 'edit'])->name('applications.edit');
    Route::put('/applications/{id}', [AdminApplicationController::class, 'update'])->name('applications.update');
    Route::delete('/applications/{id}', [AdminApplicationController::class, 'destroy'])->name('applications.destroy');
    Route::post('/applications/{id}/residency', [AdminApplicationController::class, 'updateResidency'])->name('applications.updateResidency');
    Route::post('/applications/{id}/party', [AdminApplicationController::class, 'updateParty'])->name('applications.updateParty');
    Route::post('/applications/{id}/resend-verification', [AdminApplicationController::class, 'resendVerification'])->name('applications.resendVerification');
});

// Test route for admin middleware (for testing purposes)
Route::get('/admin/test', function () {
    return response()->json(['message' => 'Admin access granted']);
})->middleware('admin');
