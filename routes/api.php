<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to AgriStat API',
        'version' => '1.0.0',
        'status' => 'active',
    ], 200);
});

// user
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware(['auth:api']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);
Route::post('/user/register', [AuthController::class, 'register'])->middleware(['auth:api']);
Route::get('/user/profile/{filename}', [AuthController::class, 'getProfileImage'])->middleware(['auth:api']); 

// get user data
Route::get('/users/list', [UserController::class, 'get'])->middleware(['auth:api'])->name('users');
// update dan delete user
Route::put('/users/edit/{user}', [UserController::class, 'update'])->middleware(['auth:api']);
Route::delete('/users/delete/{user}', [UserController::class, 'destroy'])->middleware(['auth:api']);
// check if current user is valid from token
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return response()->json($request->user());
});