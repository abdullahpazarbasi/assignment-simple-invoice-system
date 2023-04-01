<?php

use App\Http\Controllers\IndexController;
use App\Http\Controllers\InvoiceController;
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

Route::get('/', [ IndexController::class, 'index' ]);

Route::get('/users/{user}/invoices', [ InvoiceController::class, 'list' ]);

Route::get('/users/{user}/invoices/new', [ InvoiceController::class, 'getBlankDetails' ]);

Route::post('/users/{user}/invoices', [ InvoiceController::class, 'store' ]);

Route::get('/users/{user}/invoices/{invoice}', [ InvoiceController::class, 'getDetails' ])->name('single-invoice');

Route::put('/users/{user}/invoices/{invoice}', [ InvoiceController::class, 'update' ]);
