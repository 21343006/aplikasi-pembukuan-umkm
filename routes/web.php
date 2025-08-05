<?php

use App\Livewire\Capitals\CapitalCreate;
use App\Livewire\Capitals\CapitalearlyForm;
use App\Livewire\Capitals\ModalAwal;
use App\Livewire\Capitals\ModalTetap;
use App\Livewire\Dashboard;
use App\Livewire\Expenditures\ExpenditurePage;
use App\Livewire\Incomes\IncomePage;
use App\Livewire\Reports\ProfitLoss;
use App\Livewire\Reports\ReportbulananList;
use App\Livewire\Reports\ReportharianCreate;
use App\Livewire\Reports\ReportharianList;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', Dashboard::class);
Route::get('/capitals', ModalAwal::class);
Route::get('/capitals/create', CapitalCreate::class);
Route::get('/modal-awal', CapitalearlyForm::class);
Route::get('/modaltetap-page', ModalTetap::class); // Assuming this is the correct route for modal tetap

Route::get('/incomes', IncomePage::class);
Route::get('/expenditures', ExpenditurePage::class);

Route::get('/laporan-harian', ReportharianCreate::class);
Route::get('/laporan-harian/list', ReportharianList::class);
Route::get('/laporan-bulanan', ReportbulananList::class);

Route::get('profitloss', ProfitLoss::class);




