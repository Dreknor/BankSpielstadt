<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KontostandController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WorkingTimeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register' => true]);

Route::get('/kontostand', [KontostandController::class, 'kontostand'])->name('kontostand');
Route::post('/kontostand/show', [KontostandController::class, 'auth'])->name('kontostand.read_key');

Route::middleware(['auth'])->group(function (){


    Route::resource('costumer', CustomerController::class);
    Route::get('choose/customer', [CustomerController::class, 'choose']);
    Route::get('choose/customer/{customer}', [CustomerController::class, 'setCustomer']);
    Route::get('/autocomplete-search', [CustomerController::class, 'search']);

    Route::middleware(['isManager'])->group(function () {
        Route::get('create/customer', [CustomerController::class, 'createCustomer']);
        Route::post('customer/store', [CustomerController::class, 'store']);
    });

    Route::middleware(['isAdmin'])->group(function () {
        Route::get('dashboard', [AdminController::class, 'index']);
        Route::get('gebuehr', [AdminController::class, 'gebuehr']);
        Route::get('export', [AdminController::class, 'export']);
        Route::get('deleteStart', [AdminController::class, 'delete']);
        Route::get('start', [AdminController::class, 'makeStartkapital']);
        Route::get('import', [AdminController::class, 'import'])->name('import');
        Route::post('import', [AdminController::class, 'storeImport'])->name('import.store');
    });

    Route::middleware(['hasCustomer'])->group(function (){
        Route::get('new/customer', [CustomerController::class, 'new']);
        Route::get('log', [CustomerController::class, 'log']);
        Route::delete('payments/delete/{payment}', [PaymentController::class, 'delete']);
        Route::delete('working_times/delete/{working_time}', [WorkingTimeController::class, 'destroy']);

        Route::post('set/key', [CustomerController::class, 'setKey'])->name('set.key');

        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/home', [HomeController::class, 'index'])->name('home');

        Route::get('einzahlen', [PaymentController::class, 'einzahlen']);
        Route::post('einzahlen', [PaymentController::class, 'storeEinzahlen']);
        Route::get('auszahlen', [PaymentController::class, 'auszahlen']);
        Route::post('auszahlen', [PaymentController::class, 'storeAuszahlen']);
        Route::get('kredit', [PaymentController::class, 'kredit']);
        Route::post('kredit', [PaymentController::class, 'storeKredit']);


        Route::get('arbeitszeit', [WorkingTimeController::class, 'create']);
        Route::post('arbeitszeit', [WorkingTimeController::class, 'store']);

    });
});
