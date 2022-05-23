<?php

use App\Models\ChatMessage;
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

Route::prefix('auth')
//    ->middleware(['throttle:60,1'])
    ->group(function () {
        Route::post('register', 'AuthController@register');
        Route::post('login', 'AuthController@login');
        Route::post('logout', 'AuthController@logout')->middleware(['auth:api']);
        Route::post('refresh', 'AuthController@refresh');
        Route::post('password/forgot', 'AuthController@passwordForgot');

        /**
         * Social Network
         */
        Route::post('network', 'AuthController@network');
    });

Route::post('public/webinar', 'WebinarController@getPublicInfo');
Route::post('public/product', 'ProductController@getPublicInfo');
Route::post('public/product/order', 'ProductController@createOrder');

Route::middleware(['auth:api'])
    ->group(function () {
        // Me
        Route::get('/user', 'UserController@show');
        Route::post('/user', 'UserController@update');
        Route::delete('/user', 'UserController@destroy');
        Route::get('webinar/{id}/is_visited', 'WebinarController@isVisited');
    });

Route::middleware(['auth:api', 'verified_email'])
    ->group(function () {
        // Webinar
        Route::get('webinar', 'WebinarController@index');
        Route::get('webinar/{webinar}', 'WebinarController@show');
        Route::post('webinar/{id}/leave', 'WebinarController@leave');

        // Chat
        Route::get('webinar/{id}/chat', 'ChatController@show');
        Route::post('chat/{id}/message', 'ChatMessageController@store');
        Route::get('chat/{id}/message', 'ChatMessageController@index')->middleware('cacheResponse:600,' . ChatMessage::class);

        // Purchase Of Webinar Banner Items
        Route::apiResource('webinar/order', 'WebinarOrderController');
    });

Route::prefix('admin')
    ->middleware(['auth:api', 'verified_email', 'verified_phone'])
    ->namespace('\App\Http\Controllers\Admin')
    ->group(function () {
        // Account balance orders management
        Route::post('account/refill', 'AccountRefillOrderController@store');

        // Account billing
        Route::get('account/orders', 'AccountBillingController@index');
        Route::get('account/orders/search', 'AccountBillingController@search');

        // Account payment settings
        Route::put('/account/settings/payment', 'AccountController@setPaymentSettings');

        // Account facebook pixel settings
        Route::put('/account/settings/pixel', 'AccountController@setPixelSettings');
        Route::delete('/account/settings/pixel', 'AccountController@unsetPixelSettings');

        // Remove card from User Account
        Route::post('/account/card/delete', 'AccountController@deleteCard');

        // Employee management
        Route::apiResource('employee', 'EmployeeController');

        // Rooms and webinars
        Route::apiResource('room', 'RoomController');
        Route::post('room/slug', 'RoomController@generateSlug');
        Route::post('room/{room}/duplicate', 'RoomController@duplicate');
        Route::post('room/{room}/other', 'RoomController@setOtherSettings');
        Route::post('room/{room}/cover', 'RoomController@setCover');
        Route::delete('room/{room}/cover', 'RoomController@unsetCover');
        Route::post('room/{room}/pdf', 'RoomController@setPresentation');
        Route::delete('room/{room}/pdf', 'RoomController@unsetPresentation');
        Route::get('room/{room}/pdf/conversion/{conversion}', 'RoomController@getPresentationConversionStatus');

        // Script
        Route::post('room/{room}/script/import', 'RoomController@importScript');
        Route::get('room/{room}/command', 'RoomController@scriptCommandIndex');
        Route::post('room/{room}/command', 'RoomController@scriptCommandAdd');
        Route::put('room/{room}/command/{command}', 'RoomController@scriptCommandUpdate');
        Route::delete('room/{room}/command/{command}', 'RoomController@scriptCommandDelete');
        Route::delete('room/{room}/command', 'RoomController@scriptCommandsDelete');

        Route::apiResource('webinar', 'WebinarController');
        Route::get('room/{room}/webinar', 'WebinarController@index');
        Route::post('room/{room}/webinar', 'WebinarController@store');

        Route::get('webinar/{webinar}', 'WebinarController@show');
        Route::post('webinar/{webinar}/start', 'WebinarController@start');
        Route::post('webinar/{webinar}/finish', 'WebinarController@finish');
        Route::get('room/{room}/slides', 'PresentationSlidesController@index');
        Route::get('webinar/{webinar}/slides/{slide}', 'PresentationSlidesController@show');
        Route::put('webinar/{webinar}/layout', 'WebinarController@layout');
        Route::put('webinar/{webinar}/tab', 'WebinarController@tab');

        // Webinar Export Users Data
        Route::get('webinar/{webinar}/visitors/export/email/csv', 'WebinarController@exportVisitorsEmailToCSV');
        Route::get('webinar/{webinar}/visitors/export/phone/csv', 'WebinarController@exportVisitorsPhoneToCSV');
        Route::get('webinar/{webinar}/visitors/export/email_phone/csv', 'WebinarController@exportVisitorsEmailAndPhoneToCSV');

        // Room Export Webinars Users Data
        Route::get('room/{room}/visitors/export/email/csv', 'RoomController@exportVisitorsEmailToCSV');
        Route::get('room/{room}/visitors/export/phone/csv', 'RoomController@exportVisitorsPhoneToCSV');
        Route::get('room/{room}/visitors/export/email_phone/csv', 'RoomController@exportVisitorsEmailAndPhoneToCSV');

        // Product
        Route::apiResource('product', 'ProductController')->middleware('throttle:120,1');
        Route::post('product/{product}/duplicate', 'ProductController@duplicate');


        // Banner
        Route::apiResource('banner', 'BannerController')->middleware('throttle:120,1');
        Route::get('room/{room}/banner', 'BannerController@index');
        Route::put('banner/{banner}/toggle', 'BannerController@toggleVisibility')->middleware('throttle:120,1');
        Route::get('room/{room}/banner/image', 'BannerController@imageIndex');
        Route::post('room/{room}/banner/image', 'BannerController@uploadImage');
        Route::put('room/{room}/banner/{banner}/image', 'BannerController@setImage');

        // Block Chat For Everyone
        Route::put('chat/{chat}/block', 'ChatController@block');

        // Unblock Chat For Everyone
        Route::put('chat/{chat}/unblock', 'ChatController@unblock');

        // Block Chat For User
        Route::post('block/webinar/{webinar}/user/{user}', 'BlockChatUserController@store');

        // Block Chat For Users
        Route::post('chat/{chat}/message/user/block', 'ChatMessageController@blockUsers');

        // Unblock Chat For User
        Route::delete('block/webinar/{webinar}/user/{user}', 'BlockChatUserController@destroy');

        // Unblock Chat For All Users
        Route::delete('block/webinar/{webinar}/user', 'BlockChatUserController@destroyAll');

        // Unblock Chat For Listed Users
        Route::delete('block/webinar/{webinar}/users', 'BlockChatUserController@destroyFromList');

        // Ban User
        Route::post('ban/webinar/{webinar}/user/{user}', 'BanUserController@store');

        // Ban Users
        Route::post('chat/{chat}/message/user/ban', 'ChatMessageController@banUsers');

        // Unban User
        Route::delete('ban/webinar/{webinar}/user/{user}', 'BanUserController@destroy');

        // Delete Chat Message
        Route::post('chat/{chat}/message/delete', 'ChatMessageController@delete');

        // Ban Users And Delete Messages
        Route::post('chat/{chat}/message/delete/user/ban', 'ChatMessageController@banUsersAndDeleteMessages');
    });
