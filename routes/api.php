<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;

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

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
Route::get('/auth/verify-email/{email}/{otp}', [AuthController::class, 'verifyEmailLink']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Public Product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products/fetch', [ProductController::class, 'fetchProduct']);
Route::get('/products/trending', [ProductController::class, 'trending']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard/stats', [AuthController::class, 'getStats']);
    });

    // Protected Product routes
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/products/scrape', [ProductController::class, 'scrapeProduct']);

    // Cart routes
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::patch('/cart/update', [CartController::class, 'updateCartItem']);
    Route::delete('/cart/remove', [CartController::class, 'removeCartItem']);

    // Store routes
    Route::get('/stores', [StoreController::class, 'index']);
    Route::post('/stores', [StoreController::class, 'store']);
    Route::get('/stores/{id}', [StoreController::class, 'show']);
    Route::put('/stores/{id}', [StoreController::class, 'update']);
    Route::delete('/stores/{id}', [StoreController::class, 'destroy']);

    // Order routes
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    // User routes
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/user/update', [UserController::class, 'updateUser']);
});

// OTP routes
Route::post('/otp/generate', [OtpController::class, 'generate']);
Route::post('/otp/verify', [OtpController::class, 'verify']);
Route::post('/otp/resend', [OtpController::class, 'resend']);

// Test route for email
Route::get('/test-email', function () {
    try {
        $otp = '123456';
        $verificationUrl = url("/api/auth/verify-email/bomboclat7522@gmail.com/{$otp}");
        Mail::to('bomboclat7522@gmail.com')->send(new VerifyEmail($otp, $verificationUrl));
        return response()->json(['message' => 'Test email sent successfully']);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to send email',
            'error' => $e->getMessage()
        ], 500);
    }
});