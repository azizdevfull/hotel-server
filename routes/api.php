<?php

use App\Http\Controllers\Api\Mobile\Admin\AdminHotelsController;
use App\Models\Reklama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\HomeController;
use App\Http\Controllers\Api\Mobile\HotelController;
use App\Http\Controllers\Api\Mobile\PaymentController;
use App\Http\Controllers\Api\Mobile\ProfileController;
use App\Http\Controllers\Api\Mobile\HotelSearchController;
use App\Http\Controllers\Api\Mobile\UserCategoryController;
use App\Http\Controllers\Api\Mobile\Admin\ReklamaController;
use App\Http\Controllers\Api\Mobile\Admin\CategoryController;
use App\Http\Controllers\Api\Mobile\Admin\AdminUsersController;
use App\Http\Controllers\Api\Mobile\Admin\PaymentSecretController;
use App\Http\Controllers\Api\Mobile\Admin\AdminUserCategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('localization')->prefix('mobile')->group(function () {

    Route::get('/home', [HomeController::class, 'home']);
    Route::get('/categories', [UserCategoryController::class, 'index']);
    Route::get('/categories/{category}', [UserCategoryController::class, 'showCategory']);
    Route::get('/hotels', [HotelController::class, 'index']);
    Route::get('/hotels/{hotels}', [HotelController::class, 'show']);
    
    Route::get('/reklama', [ReklamaController::class, 'index']);

    // Hotel Search
    Route::get('/search', [HotelSearchController::class, 'index']);
 

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/logout', [AuthController::class, 'logoutUser'])->middleware('auth:sanctum');

    Route::post('/verify', [AuthController::class, 'verifySms']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/resend-code', [AuthController::class, 'resendSms']);

    Route::get('/profile/{user}', [ProfileController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [ProfileController::class, 'Profile']);
        Route::post('/profile-update', [ProfileController::class, 'ProfileUpdate']);

        // Payment Routes
        Route::post('/pay', [PaymentController::class, 'pay']);

        // Hotel Routes
        Route::post('/hotels', [HotelController::class, 'store']);
        Route::post('/hotels/{hotel}', [HotelController::class, 'update']);
        Route::put('/hotels/{hotel}', [HotelController::class, 'update']);
        Route::delete('/hotels/{hotel}', [HotelController::class, 'destroy']);
    });

    // Admin Routes
    Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('payment-secrets', PaymentSecretController::class);

        // Reklama Routes
        Route::apiResource('reklama', ReklamaController::class);
        Route::post('reklama/{reklama}', [ReklamaController::class, 'update']);

        // Users Routes
        Route::post('users/{user}', [AdminUsersController::class, 'update']);
        Route::apiResource('users', AdminUsersController::class);

        // Hotels Routes
        Route::post('hotels/{hotels}', [AdminHotelsController::class, 'update']);
        Route::apiResource('hotels', AdminHotelsController::class);
    });

});
