<?php

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

use Illuminate\Support\Facades\Route;

/*
 * Webhooks
 */
Route::prefix('cloudpayments')->namespace('Webhooks')
    ->group(function () {
        Route::post('check', 'CloudPaymentsController@check');
        Route::post('pay', 'CloudPaymentsController@pay');
        Route::post('fail', 'CloudPaymentsController@fail');
        Route::post('cancel', 'CloudPaymentsController@cancel');
        Route::post('refund', 'CloudPaymentsController@refund');
    });