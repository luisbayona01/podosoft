<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TenantRegistrationController;
use App\Livewire\Dashboard;
use App\Http\Controllers\ClinicalPhotoController;
use App\Http\Controllers\PaymentReceiptController;
use App\Livewire\AppointmentIndex;
use App\Livewire\AppointmentManager;
use App\Livewire\PaymentRegister;
use App\Livewire\PatientIndex;
use App\Livewire\PatientCreate;
use App\Livewire\PatientShow;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ChangePassword;
use App\Livewire\Auth\Onboarding;
use App\Livewire\CategoryAttributeManager;
use App\Livewire\ClinicalHistoryMain;
use App\Livewire\ClinicalHistoryIndex;
use App\Livewire\ClinicalHistoryCreate;
use App\Livewire\FootMap;
use App\Livewire\FinancialsReport;
use App\Livewire\InsumoIndex;
use App\Livewire\InsumoCreate;
use App\Livewire\InsumoEdit;
use App\Livewire\ServicioIndex;
use App\Livewire\ServicioCreate;
use App\Livewire\ServicioEdit;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', Login::class)->name('login');
Route::get('/register', function () { return view('onboarding'); })->name('register');
Route::post('/register/submit', [OnboardingController::class, 'submit'])->name('register.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/profile/password', ChangePassword::class)->name('profile.password');
    Route::get('/diagnostico-plantillas', \App\Livewire\DiagnosticoPlantillaManager::class)->name('diagnostico-plantillas');
    Route::get('/category-attributes/{categoria_id}', CategoryAttributeManager::class)->name('category-attributes.manage');
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/create', PaymentRegister::class)->name('create');
        Route::get('/{id}/edit', PaymentRegister::class)->name('edit');
        Route::get('/{id}/receipt', [PaymentReceiptController::class, 'show'])->name('receipt');
    });

    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', AppointmentIndex::class)->name('index');
        Route::get('/create', AppointmentManager::class)->name('create');
        Route::get('/{id}/edit', AppointmentManager::class)->name('edit');
    });

    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', PatientIndex::class)->name('index');
        Route::get('/create', PatientCreate::class)->name('create');
        Route::get('/{patient}/edit', \App\Livewire\PatientEdit::class)->name('edit');
        Route::get('/{patient}', PatientShow::class)->name('show');
    });

    Route::prefix('clinical-history')->name('clinical-history.')->group(function () {
        Route::get('/', ClinicalHistoryMain::class)->name('index');
        Route::get('/create', ClinicalHistoryCreate::class)->name('create');
        Route::get('/foot-map', FootMap::class)->name('foot-map');
        Route::get('/photo/{photo}', [ClinicalPhotoController::class, 'show'])->name('photo');
        Route::get('/photo/{photo}/download', [ClinicalPhotoController::class, 'download'])->name('photo.download');
    });

    Route::prefix('financials')->name('financials.')->group(function () {
        Route::get('/', FinancialsReport::class)->name('report');
        Route::get('/register/{citaId}', PaymentRegister::class)->name('payment.register');
    });

    Route::prefix('insumos')->name('insumos.')->group(function () {
        Route::get('/', InsumoIndex::class)->name('index');
        Route::get('/create', InsumoCreate::class)->name('create');
        Route::get('/{insumo}/edit', InsumoEdit::class)->name('edit');
    });
});
