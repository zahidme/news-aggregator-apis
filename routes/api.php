<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\UserPreferenceController;
Route::middleware('throttle:60,1')->group(function () {
Route::controller(RegisterController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::middleware('auth:sanctum')->post('logout', [RegisterController::class, 'logout']);
    Route::post('password/reset', [RegisterController::class, 'resetPassword']);
});


Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{id}', [ArticleController::class, 'show']);
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/user/preferences', [UserPreferenceController::class, 'setPreferences']);
    Route::get('/user/preferences', [UserPreferenceController::class, 'getPreferences']);
    Route::get('/user/personalized-feed', [UserPreferenceController::class, 'personalizedFeed']);
});
});
