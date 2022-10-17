<?php

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

/**
 * route "/register"
 * @method "POST"
 */
Route::post('/register', 'Api\RegisterController')->name('register');

/**
 * route "/login"
 * @method "POST"
 */
Route::post('/login', 'Api\LoginController')->name('login');
Route::post('/status/{service}/{messageId}', 'UpdateController@getStatus');

/**
 * route using middleware
 */
Route::group(['middleware' => 'auth:api'], function () {
    /**
     * User
     */
    Route::post('/user/profile', 'Api\UserController@profile')->name('profile');
    Route::post('/user/change-password', 'Api\UserController@changePassword')->name('change-password');

    /**
     * Order
     */
    Route::post('/order', 'Api\OrderController@order')->name('profile');

    Route::post('/logout', 'Api\LogoutController')->name('logout');
});