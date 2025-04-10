<?php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrestamosController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ReportPayController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$routes = function () {
    Route::get('/', function () {
        return view('welcome');
    });

    /*Route::get('/admin/createAdminUser', function () {
        $user = new User();
        $user->name = 'Administrator';
        $user->username = 'Administrator';
        $user->password = Hash::make('Password1');
        $user->email = 'desarrolladorob@corporacionob.com';
        $user->save();
        return response()->json($user, 200);
    });*/

    // Ruta corregida (agrega el parámetro {prestamo} a la URL)
    Route::get('/loan/{prestamo}', [ReportPayController::class, 'generateReport'])->name('pay.report');
};

Route::middleware('custom.throttle')->group(function () {
    // Rutas que requieren limitación de tasa
});

Route::get('change-language/{lang}', [LanguageController::class, 'changeLanguage'])
    ->name('change.language');

Route::group(['prefix' => ''], $routes);
Route::group(['prefix' => 'ProyectoLaravel'], $routes);
