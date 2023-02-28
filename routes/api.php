<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\userController;

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

Route::controller(userController::class)->group(function(){
    // Route::post('user_register', 'register');
    Route::post('/', 'authenticateWithPhone');
    Route::post('/resend-otp', 'resendOtp');
    Route::post('otp-verification', 'otpVerification');
});

Route::middleware('auth:sanctum')->group( function () {
    Route::get('/get-loggedin-user', [userController::class, 'getLoggedInUser']);
    Route::post('/update-profile/{id}', [userController::class, 'updateProfile']); 
});




