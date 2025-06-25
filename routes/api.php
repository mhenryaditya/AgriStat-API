<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\ClusteringController;
use App\Http\Controllers\CropsProductionController;
use App\Http\Controllers\PredictionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;

// Route::prefix('api')->group(function () {
//     Route::get('/', function () {
//         return response()->json([
//             'message' => 'Welcome to AgriStat API',
//             'version' => '1.0.0',
//             'status' => 'active',
//         ], 200);
//     });

//     // crops
//     Route::post('/crops/import', [CropsProductionController::class, 'import'])->middleware(['auth:api']);
//     Route::resource('/crops', CropsProductionController::class)->except(['create', 'edit'])->middleware(['auth:api']);
//     Route::get('/statistik/client', [CropsProductionController::class, 'getDataForStats']);

//     // user
//     Route::post('/auth/login', [AuthController::class, 'login']);
//     Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware(['auth:api']);
//     Route::post('/auth/refresh', [AuthController::class, 'refresh']);
//     Route::post('/user/register', [AuthController::class, 'register'])->middleware(['auth:api']);
//     Route::get('/user/profile/{filename}', [AuthController::class, 'getProfileImage'])->middleware(['auth:api']);

//     // get user data
//     Route::get('/users/list', [UserController::class, 'get'])->middleware(['auth:api'])->name('users');
//     // get user image name
//     Route::get('/profile/image/{user}', [UserController::class, 'getImageName'])->middleware(['auth:api']);
//     // update user
//     Route::put('/users/edit/{user}', [UserController::class, 'update'])->middleware(['auth:api']);
//     // update users profile 
//     Route::put('/profile/edit-profile/{user}', [UserController::class, 'updateProfile'])->middleware(['auth:api']);
//     Route::put('/profile/edit-password/{user}', [UserController::class, 'updatePassword'])->middleware(['auth:api']);
//     Route::post('/profile/edit-image/{user}', [UserController::class, 'updateImage'])->middleware(['auth:api']);
//     // delete user
//     Route::delete('/users/delete/{user}', [UserController::class, 'destroy'])->middleware(['auth:api']);
//     // check if current user is valid from token
//     Route::middleware('auth:api')->get('/user', function (Request $request) {
//         return response()->json($request->user());
//     });
//     // Predict
//     Route::post('/train-models', [PredictionController::class, 'trainModels'])->middleware(['auth:api']);
//     Route::post('/predict', [PredictionController::class, 'predict'])->middleware(['auth:api']);
// });

Route::any('{any}', function (Request $request) {
    $data = [
        'message' => 'This is a debug response.',
        'path_laravel_sees' => $request->path(),
        'full_url' => $request->fullUrl(),
    ];

    // Use a simple json response as dd() might not work well in Vercel's environment
    return response()->json($data, 200, [], JSON_PRETTY_PRINT);

})->where('any', '.*');