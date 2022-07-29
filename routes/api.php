<?php

use App\Http\Controllers\API\VoucherGiftController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('validate-photo', [VoucherGiftController::class, 'validatePhoto'])->name('validatePhoto');
Route::post('customer-check', [VoucherGiftController::class, 'eligibleCheck'])->name('eligibleCheck');
