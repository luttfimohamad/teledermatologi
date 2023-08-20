<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('guest')->group(function () {
    Route::post('admin/login', [AdminController::class, 'login'])->name('admin.login'); //testing aman
    Route::post('doctor/login', [DoctorController::class, 'login'])->name('doctor.login'); //testing aman
    Route::post('patient/register', [PatientController::class, 'register'])->name('patient.register'); //testing aman
    Route::post('patient/login', [PatientController::class, 'login'])->name('patient.login'); //testing aman
});

Route::middleware('auth:apiadmin')->group(function () {
    // for admin
    Route::get('admins', [AdminController::class, 'get_all_admin'])->name('admins.dashboard'); //testing aman
    Route::get('admin/{id}', [AdminController::class, 'get_a_singgle_admin'])->name('admin.dashboard'); //testing aman
    Route::post('admin/add/admin', [AdminController::class, 'add_admin'])->name('admin.addAdmin'); //testing aman
    Route::put('admin/update/admin/{id}', [AdminController::class, 'update_admin'])->name('admin.updateAdmin'); //testing aman
    Route::delete('admin/delete/admin/{id}', [AdminController::class, 'delete_admin'])->name('admin.deleteAdmin'); //testing aman
    Route::post('admin', [AdminController::class, 'logout'])->name('admin.logout'); //testing aman

    // for doctor
    Route::post('admin/add/doctor', [AdminController::class, 'add_doctor'])->name('admin.addDoctor'); //testing aman
    Route::put('admin/update/doctor/{id}', [AdminController::class, 'update_doctor'])->name('admin.updateDoctor'); //testing aman
    Route::delete('admin/delete/doctor/{id}', [AdminController::class, 'delete_doctor'])->name('admin.deleteDoctor'); //testing aman

    // for patient
    Route::put('admin/update/patient/{id}', [AdminController::class, 'update_patient'])->name('admin.updatePatient'); //testing aman
    Route::delete('admin/delete/patient/{id}',  [AdminController::class, 'delete_patient'])->name('admin.deletePatient'); //testing aman
});

Route::middleware('auth:apidoctor')->group(function () {
    Route::get('doctors', [DoctorController::class, 'get_all_doctors'])->name('doctors.dashboard'); //testing aman
    Route::get('doctor/{id}', [DoctorController::class, 'get_a_singgle_doctor'])->name('doctor.dashboard'); //testing aman
    Route::put('doctor/update', [DoctorController::class, 'update'])->name('doctor.update'); //testing aman
    Route::put('doctor/change_password', [DoctorController::class, 'change_password'])->name('doctor.changePassword'); //testing aman
    Route::post('doctor', [DoctorController::class, 'logout'])->name('doctor.logout'); //testing aman
});

Route::middleware('auth:apipatient')->group(function () {
    Route::get('patients', [PatientController::class, 'get_all_patients'])->name('patients.dashboard'); //testing aman
    Route::get('patient/{id}', [PatientController::class, 'get_a_singgle_patient'])->name('patient.dashboard'); //testing aman
    Route::put('patient/update', [PatientController::class, 'update'])->name('patient.update'); //testing aman
    Route::put('patient/change_password', [PatientController::class, 'change_password'])->name('patient.changePassword'); //testing aman
    Route::post('patient', [PatientController::class, 'logout'])->name('patient.logout'); //testing aman
});
