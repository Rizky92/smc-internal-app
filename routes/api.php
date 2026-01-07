<?php

use App\Http\Controllers\API\AntreanLoketController;
use App\Http\Controllers\API\CreateSPOController;
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

Route::get('/', CreateSPOController::class)->name('create-spo');

Route::post('/panggil-antrean-loket-smc', [AntreanLoketController::class, 'call'])
    ->name('panggil-antrean-loket-smc');

Route::post('/stop-antrean-loket-smc', [AntreanLoketController::class, 'stop'])
    ->name('stop-antrean-loket-smc');
