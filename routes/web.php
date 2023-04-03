<?php

declare(strict_types=1);

use App\Http\Controllers\UserWebAppController;
use App\Http\Controllers\InvoiceWebAppController;
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

Route::get('/', [UserWebAppController::class, 'list']);

Route::get('/users/{user}/invoices', [InvoiceWebAppController::class, 'list']);

Route::get('/users/{user}/invoices/new', [InvoiceWebAppController::class, 'getBlankDetails']);

Route::post('/users/{user}/invoices', [InvoiceWebAppController::class, 'store']);

Route::get('/users/{user}/invoices/{invoice}', [InvoiceWebAppController::class, 'getDetails'])->name('single-invoice');

Route::put('/users/{user}/invoices/{invoice}', [InvoiceWebAppController::class, 'update']);
