<?php

Route::group(['namespace' => 'MG\MoMo\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => 'payments'], function () {
        Route::get('check-payment-momo', 'MomoPaymentController@checkStatusPaymentMomo')->name('payments.momo.status');
    });
});
Route::group(['namespace' => 'MG\MoMo\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::get('momo-balance', 'MomoPaymentController@momoBalance')->name('payments.momo.balance');
        Route::post('momo-withdraw', 'MomoPaymentController@withdrawBalance')->name('payments.momo.withdraw');
    });
});
