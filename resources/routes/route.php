<?php
Route::group(['middleware' => 'web'], function () {
    Route::group(['prefix' => config('voyager.user.redirect')], function () {
        // extends `/payments/`
        Route::group(['as' => 'voyager.', 'middleware' => 'admin.user', 'namespace' => 'T2G\Common\Controllers'], function () {
            Route::group(['as' => 'payments.', 'prefix' => '/payments'], function () {
                Route::get('/{user}/history', [
                    'uses' => 'Admin\PaymentBreadController@history',
                    'as' => 'history'
                ]);
                Route::get('/{payment}/accept', [
                    'uses' => 'Admin\PaymentBreadController@accept',
                    'as' => 'accept',
                ]);
                Route::get('/{payment}/reject', [
                    'uses' => 'Admin\PaymentBreadController@reject',
                    'as' => 'reject',
                ]);
                Route::get('/report', [
                    'uses' => 'Admin\PaymentBreadController@report',
                    'as' => 'report',
                ]);
            });
        });

        // extends `/users/`
        Route::group(['as' => 'voyager.', 'middleware' => 'admin.user', 'namespace' => 'T2G\Common\Controllers'], function () {
            Route::group(['as' => 'users.', 'prefix' => '/users'], function () {
                Route::get('/report', [
                    'uses' => 'Admin\UserBreadController@report',
                    'as' => 'report',
                ]);
            });
        });

        // extends `/users/`
        Route::group(['as' => 'voyager.', 'middleware' => 'admin.user', 'namespace' => 'T2G\Common\Controllers'], function () {
            Route::group(['as' => 'ccus.', 'prefix' => '/ccu'], function () {
                Route::get('/report', [
                    'uses' => 'Admin\CCUController@report',
                    'as' => 'report',
                ]);
                Route::get('/tick', [
                    'uses' => 'Admin\CCUController@tick',
                    'as' => 'tick',
                ]);
            });
        });

        Voyager::routes();
        // Voyager overwritten routes
        Route::group(['as' => 'voyager.', 'middleware' => 'admin.user', 'namespace' => 'T2G\Common\Controllers'], function () {
            Route::get('/', ['uses' => 'Admin\DashboardController@index', 'as' => 'dashboard']);
        });
    });

    Route::group(['prefix' => 'autocomplete', 'as' => 'autocomplete.', 'namespace' => 'T2G\Common\Controllers'], function () {
        Route::get('/users', ['uses' => 'AutoCompleteController@getUsers', 'as' => 'users']);
    });

});
