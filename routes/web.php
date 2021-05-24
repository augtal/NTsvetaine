<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

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

    Route::get('/deleteUser/{id}', 'UserController@deleteUser');

    Route::get('/archive/{id}', 'AdvertisementController@archiveAdvertisement');
});

Route::middleware(['auth', 'user'])->group(function () {
    Route::get('profile', 'UserController@showProfilePage');

    Route::get('profileEditPage', 'UserController@showEditPage');

    Route::post('profile/password-change', 'UserController@changePassword');

    Route::get('likedListings', 'UserController@showLikedAdsPage');

    Route::post('/listing/{id}/fav', 'AdvertisementController@favoritePage');

    Route::post('/showSaveNotification', 'NotificationController@showNotificationConfirmPage');

    Route::post('/saveNotification', 'NotificationController@saveNotification');

    Route::get('/testFunction', 'WebScrapperController@summonMainMethod');

    Route::get('/notifications', 'NotificationController@showNotificationsList');

    Route::get('/notification/{id}', 'NotificationController@showNotification');

    Route::get('/notification/{id}/edit', 'NotificationController@showEditNotificationPage');

    Route::post('/notification/{id}/saveEdit', 'NotificationController@editNotification');

    Route::get('/notification/{id}/delete', 'NotificationController@deleteNotification');

    Route::get('/markAllMessagesRead', 'UserController@markAllMessagesRead');

    Route::get('/markMessageRead/{id}', 'UserController@markMessageRead');
});

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');


Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/home');
})->middleware(['auth', 'signed'])->name('verification.verify');


Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');