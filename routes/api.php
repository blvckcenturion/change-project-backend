<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PetitionController;
use App\Http\Controllers\CommentController;


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

Route::group(['prefix' => 'users'], function ($router) {
    Route::post('/register', [UserController::class, 'register'])->name('register.user');
    Route::post('/login', [UserController::class, 'login'])->name('login.user');
    Route::get('/view-profile', [UserController::class, 'viewProfile'])->name('profile.user');
    Route::get('/logout', [UserController::class, 'logout'])->name('logout.user');
});

Route::group(['prefix' => 'petition'], function($router) {
    Route::get('/', [PetitionController::class, 'getAllPetitions']);
    Route::get('/{id}', [PetitionController::class, 'getPetition']);
    Route::post('/', [PetitionController::class, 'postPetition']);
    Route::put('/{id}', [PetitionController::class, 'signPetition']);
    Route::delete('/{id}', [PetitionController::class, 'deletePetition']);
});

Route::group(['prefix' => 'comments'], function($router) {
    Route::get('/{id}', [CommentController::class, 'getPetitionComments']);
    Route::post('/', [CommentController::class, 'postComment']);
    Route::delete('/{id}', [CommentController::class, 'deleteComment']); 
});

Route::group(['prefix' => 'signed'], function($router) {
    Route::get('/user/{id}', [PetitionController::class, 'getUserSigned']);
    Route::post('/', [PetitionController::class, 'postSigned']);
    Route::delete('/', [PetitionController::class, 'deleteSigned']);
});