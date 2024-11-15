<?php
use App\Http\Controllers\ReportPayController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Ruta para el recurso Loan
Route::resource('loans', ReportPayController::class);
Route::get('/loan/{loan}/report', [ReportPayController::class, 'generateReport'])->name('pay.report');

