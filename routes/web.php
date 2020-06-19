<?php

/**
 * Данная группа маршрутов имеет общие:
 *      - префикс: `g_recaptcha`;
 *      - посредники: `web`.
 */
Route::prefix('g_recaptcha')
    ->namespace('Russsiq\GRecaptcha\Http\Controllers')
    ->middleware([
        'web',
    ])
    ->group(function () {
        Route::get('image-code', 'ImageCodeController@show')
            ->name('g_recaptcha.image_code');
    });
