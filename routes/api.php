<?php

use App\Http\Controllers\Auth\JWTController;
use App\Http\Controllers\User\CountryController;
use App\Http\Controllers\User\ProfessionController;
use App\Http\Controllers\User\UserProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\NewsController;
use App\Http\Controllers\User\CategoryController;
use App\Http\Controllers\User\SubCategoryController;
use App\Http\Controllers\User\ResourceController;
use App\Http\Controllers\User\AdvertisementController;
use App\Http\Controllers\User\UserController;

Route::group([], function($router) {
    // AUTHORIZATION PART
    Route::group(['prefix' => 'auth'], function() {
        Route::post('/register', [JWTController::class, 'register']);
        Route::post('/forgot-password', [JWTController::class, 'forgotPassword']);
        Route::post('/login', [JWTController::class, 'login']);
        Route::post('/accept-email', [JWTController::class, 'verifyAccount']);
        Route::post('/reset-password', [JWTController::class, 'resetPassword']);
        Route::post('/logout', [JWTController::class, 'logout']);
        Route::post('/refresh', [JWTController::class, 'refresh']);
        Route::post('/profile', [JWTController::class, 'profile']);
    });

    // ADMIN PART
    Route::group(['prefix' => 'admin'], function() {
        Route::group(['prefix' => 'news'], function() {
            Route::get('/',[NewsController::class, 'indexAdmin']);
            Route::get('/{id}',[NewsController::class, 'single']);
            Route::post('/create',[NewsController::class, 'create']);
            Route::delete('/{id}',[NewsController::class, 'delete']);
        });

        Route::group(['prefix' => 'resource'], function() {
            Route::post('/', [ResourceController::class, 'create']);
        });

        Route::group(['prefix' => 'user'], function() {
            Route::get('/not-approved',[UserController::class, 'indexAdminNotApproved']);
            Route::get('/not-approved/{id}',[UserController::class, 'singleAdminNotApproved']);
            Route::get('/change-status/{id}',[UserController::class, 'updateModerationStatus']);
            Route::get('/',[UserController::class, 'index']);
            Route::get('/{id}',[UserController::class, 'single']);
        });

        // Advertisements
        Route::group(['prefix' => 'advertisement'], function() {
            Route::get('/not-approved', [AdvertisementController::class, 'indexAdmin']);
            Route::get('/not-approved/{id}',[AdvertisementController::class, 'singleAdmin']);
            Route::patch('/change-status/{id}',[AdvertisementController::class, 'updateModerationStatus']);
        });
        // Categories
        Route::group(['prefix' => 'category'], function() {
            Route::get('/',[CategoryController::class, 'indexAdmin']);
            Route::get('/{id}',[CategoryController::class, 'single']);
            Route::post('/create',[CategoryController::class, 'create']);
            Route::delete('/{id}',[CategoryController::class, 'delete']);
            // Sub Categories
            Route::get('/{id}/sub-categories',[SubCategoryController::class, 'index']);
            Route::post('/{id}/sub-categories/create',[SubCategoryController::class, 'create']);
            Route::delete('/sub-categories/{id}',[SubCategoryController::class, 'delete']);
        });

        // Professions
        Route::group(['prefix' => 'professions'], function() {
            Route::get('/',[ProfessionController::class, 'indexAdmin']);
            Route::post('/create',[ProfessionController::class, 'create']);
            Route::delete('/{id}',[ProfessionController::class, 'delete']);
        });

    });
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// ///////////////////////////////////////////////////////////////////////////////////////////
//     USER PART
    Route::group(['prefix' => 'resource'], function() {
        Route::post('/', [ResourceController::class, 'create']);
    });

    Route::group(['prefix' => 'profile'], function() {
        Route::post('/filter',[UserProfileController::class, 'index']);
        Route::post('/castings/filter',[UserProfileController::class, 'castings']);
        Route::get('/search',[UserProfileController::class, 'searchProfile']);
        Route::get('/',[UserProfileController::class, 'single']);
        Route::get('/{userId}',[UserProfileController::class, 'singleById']);
        Route::put('/settings',[UserProfileController::class, 'updateSettings']);
        Route::put('/works',[UserProfileController::class, 'updateWorks']);
        Route::put('/image',[UserProfileController::class, 'updateProfileImage']);
    });

    Route::get('countries', [CountryController::class, 'index']);

    Route::group(['prefix' => 'category'], function() {
        Route::post('/',[CategoryController::class, 'index']);
        // Sub Categories
        Route::get('/sub-categories/{id}',[SubCategoryController::class, 'single']);
    });

    // Professions
    Route::group(['prefix' => 'professions'], function() {
        Route::get('/',[ProfessionController::class, 'index']);
        Route::get('/{id}',[ProfessionController::class, 'single']);
    });

    Route::group(['prefix' => 'news'], function() {
        Route::post('/',[NewsController::class, 'index']);
    });
    // Advertisements
    Route::group(['prefix' => 'advertisement'], function() {
        Route::post('/filter',[AdvertisementController::class, 'index']);
        Route::get('/{id}/profile',[AdvertisementController::class, 'getProfileForSingleAdvertisement']);
        Route::get('/filter/my',[AdvertisementController::class, 'userAdvertisements']);
        Route::get('/{id}',[AdvertisementController::class, 'single']);
        Route::post('/create',[AdvertisementController::class, 'create']);
        Route::delete('/delete/{id}',[AdvertisementController::class, 'delete']);
    });

});
