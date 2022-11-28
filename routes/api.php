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
Route::post('/user/register', 'Api\RegisterController')->name('register');

/**
 * route "/login"
 * @method "POST"
 */
Route::post('/user/login', 'Api\LoginController')->name('login');
Route::post('/status/{service}/{messageId}', 'UpdateController@getStatus');
Route::get('/status', 'UpdateController@updateAllStatus');
Route::get('/sendOrder', 'UpdateController@sendOrder');
Route::get('/admin/balance', 'UpdateController@balance');

/**
 * route using middleware
 */
Route::group(['middleware' => 'jwt.verify'], function () {
    /**
     * User
     */
    Route::post('/user/profile', 'Api\UserController@profile')->name('profile');
    Route::post('/user/change/password', 'Api\UserController@changePassword')->name('change-password');
    Route::post('/user/change/profile', 'Api\UserController@changeProfile')->name('change-profile');
    Route::post('/user/change/key', 'Api\UserController@changeAPI')->name('change-key');
    Route::post('/user/logout', 'Api\UserController@logout')->name('logout');

    /**
     * Service
     */
    Route::post('/admin/service', 'Api\Admin\ServiceController@add')->name('add-service');
    Route::get('/admin/services', 'Api\Admin\ServiceController@getServices')->name('get-service');
    Route::get('/admin/service/{name}', 'Api\Admin\ServiceController@getServicesByName')->name('get-service-by-name');
    Route::post('/admin/service/edit/{name}', 'Api\Admin\ServiceController@edit')->name('edit-service-by-name');
    Route::post('/admin/service/delete', 'Api\Admin\ServiceController@delete')->name('delete-service');

    /**
     * Order
     */
    Route::post('/order', 'Api\OrderController@order')->name('order');
    Route::get('/orders', 'Api\OrderController@getOrder')->name('get-order');
    Route::get('/orders/{find}', 'Api\OrderController@getOrderByID')->name('get-order-by-id');
    Route::post('/order/update/{find}', 'Api\OrderController@updateOrder')->name('update-order');
    Route::post('/order/cancel', 'Api\OrderController@cancelOrder')->name('cancel-order');

    /**
     * Contact
     */
    Route::post('/contact', 'Api\ContactController@create')->name('add-contact');
    Route::get('/contacts', 'Api\ContactController@show')->name('list-contact');
    Route::get('/contacts/{find}', 'Api\ContactController@searchContact')->name('search-contact');
    Route::post('/contact/edit/{find}', 'Api\ContactController@editContact')->name('edit-contact');
    Route::post('/contact/delete', 'Api\ContactController@deleteContact')->name('delete-contact');
});