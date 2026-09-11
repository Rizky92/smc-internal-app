<?php

use App\Http\Controllers\API\CreateSPOController;
use App\Http\Controllers\API\DicomRouterWebhookController;
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

/*
 * Penerima webhook SATUSEHAT DICOM Router. Router ada di jaringan lokal rumah
 * sakit dan memanggil endpoint ini lewat IP LAN, bukan lewat internet.
 *
 * Throttle grup api (60/menit) sengaja dilepas: pengiriman router bersifat
 * fire-and-forget tanpa retry, sehingga satu response 429 berarti hasil
 * pengiriman DICOM tersebut hilang permanen.
 */
Route::post('webhook/dicom-router', DicomRouterWebhookController::class)
    ->name('webhook.dicom-router')
    ->withoutMiddleware('throttle:60,1')
    ->middleware(['throttle:600,1', 'dicom-router.webhook']);
