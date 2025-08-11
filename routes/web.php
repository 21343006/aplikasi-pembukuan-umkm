<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Beps\BepForm;
use App\Livewire\Capitals\CapitalearlyForm;
use App\Livewire\Capitals\ModalPage;
use App\Livewire\Expenditures\ExpenditurePage;
use App\Livewire\Capitals\FixedCostPage;
use App\Livewire\Incomes\IncomePage;
use App\Livewire\Reports\IrrPage;
use App\Livewire\Reports\ProfitLoss;
use App\Livewire\Reports\ReportbulananList;
use App\Livewire\Reports\ReportharianPage;
use App\Livewire\Reports\Reporttahunan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});

// Guest routes (untuk user yang belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// Logout route
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    
    return redirect('/login');
})->middleware('auth')->name('logout');

// Protected routes (untuk user yang sudah login)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard::class)->name('dashboard');
    
    // UMKM Management Routes
    Route::get('/modal-page', ModalPage::class)->name('modal.page');
    Route::get('/modal-awal', CapitalearlyForm::class)->name('modal.awal');
    Route::get('/fixed-cost-page', FixedCostPage::class)->name('fixed.cost');
    
    // Transactions Routes
    Route::get('/incomes', IncomePage::class)->name('incomes');
    Route::get('/expenditures', ExpenditurePage::class)->name('expenditures');
    
    // Reports Routes
    Route::get('/laporan-harian', ReportharianPage::class)->name('laporan.harian');
    Route::get('/laporan-bulanan', ReportbulananList::class)->name('laporan.bulanan');
    Route::get('/report-tahunan', Reporttahunan::class)->name('laporan.tahunan');
    Route::get('/profitloss', ProfitLoss::class)->name('profitloss');
    
    // Analysis Routes
    Route::get('/bep-form', BepForm::class)->name('bep.form');
    Route::get('/irr-analysis', IrrPage::class)->name('irr.analysis');
});