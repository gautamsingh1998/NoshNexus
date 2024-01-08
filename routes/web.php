<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});
    
    //Route::post('register', 'AuthController@register');
//Route::post('login', 'AuthController@login');
    Route::group([
    'prefix' => 'api',
], function ($router) {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');

    # Send forget password otp to user email
    Route::post('forget-password','AuthController@forgetPassword');

    # Reset Password
    Route::post('reset-password','AuthController@resetPassword');
    Route::post('verify-otp','AuthController@verifyOtp');

    Route::group(['middleware' => 'auth:api'], function() {
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::post('me', 'AuthController@me');

    });

});