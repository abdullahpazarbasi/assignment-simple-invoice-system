<?php

declare(strict_types=1);

use App\Http\Controllers\UserWebAppFrontController;
use App\Http\Controllers\InvoiceWebAppFrontController;
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

Route::get('/', [UserWebAppFrontController::class, 'list']);

Route::get('/users/{user}/invoices', [InvoiceWebAppFrontController::class, 'list']);

Route::post('/users/{user}/invoices', [InvoiceWebAppFrontController::class, 'store']);

Route::get('/users/{user}/invoices/{invoice}', [InvoiceWebAppFrontController::class, 'getDetails'])->name('single-invoice');

Route::put('/users/{user}/invoices/{invoice}', [InvoiceWebAppFrontController::class, 'update']);
