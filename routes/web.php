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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

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
});