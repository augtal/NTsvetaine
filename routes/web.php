<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', 'AdvertisementController@showAdvertisementList');

Route::get('/home', 'AdvertisementController@showAdvertisementList')->name('home');

Route::get('/listingsList', 'AdvertisementController@showAdvertisementList')->name('advertisementList');

Route::get('/listing/{id}', 'AdvertisementController@showAdvertisement')->name('advertisement');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/scrapper', 'WebScrapperController@index')->name('scrapper');

    Route::get('/userList', 'UserController@showUserList');

    Route::get('/changeRole/{id}', 'UserController@changeUserRole');
});

Route::middleware(['auth', 'user'])->group(function () {
    Route::get('profile', 'UserController@showProfilePage');

    Route::get('profileEditPage', 'UserController@showEditPage');

    Route::post('profile/password-change', 'UserController@changePassword');

    Route::get('likedListings', 'UserController@showLikedAdsPage');

    Route::post('/listing/{id}/fav', 'AdvertisementController@favoritePage');

    Route::post('/showSaveNotification', 'NotificationController@showNotificationConfirmPage');

    Route::post('/saveNotification', 'NotificationController@saveNotification');

    Route::get('/notifications', 'NotificationController@showNotificationsList');

    Route::get('/notification/{id}', 'NotificationController@showNotification');

    Route::get('/notification/{id}/edit', 'NotificationController@showEditNotificationPage');

    Route::post('/notification/{id}/saveEdit', 'NotificationController@editNotification');

    Route::get('/notification/{id}/delete', 'NotificationController@deleteNotification');

    Route::get('/markMsgsRead', 'UserController@markAllMsgAsRead');
});