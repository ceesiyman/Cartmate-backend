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
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Auth Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
Route::get('/auth/verify-email/{email}/{otp}', [AuthController::class, 'verifyEmailLink']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Public Product Routes
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products/fetch', [ProductController::class, 'fetchProduct']);
Route::get('/products/trending', [ProductController::class, 'trending']);

// OTP Routes
Route::post('/otp/generate', [OtpController::class, 'generate']);
Route::post('/otp/verify', [OtpController::class, 'verify']);
Route::post('/otp/resend', [OtpController::class, 'resend']);

// Test Email Route
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

// Protected Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // Admin
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard/stats', [AuthController::class, 'getStats']);
        Route::get('/dashboard/recent-orders', [AuthController::class, 'recentOrders']);
        Route::get('/dashboard/pending-actions', [AuthController::class, 'pendingActions']);
        Route::get('/dashboard/analytics', [AuthController::class, 'orderAnalytics']);
        Route::get('/dashboard/order-volume', [AuthController::class, 'orderVolume']);
        Route::get('/dashboard/revenue-by-category', [AuthController::class, 'revenueByCategory']);
         Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);

    });

    // Product Management
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/products/scrape', [ProductController::class, 'scrapeProduct']);

    // Cart
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::patch('/cart/update', [CartController::class, 'updateCartItem']);
    Route::put('/cart/update/{id}', [CartController::class, 'updateCartItem']); // Optional alternative
    Route::delete('/cart/remove', [CartController::class, 'removeCartItem']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart']);
    Route::delete('/cart/clear', [CartController::class, 'clearCart']);

    // Store
    Route::get('/stores', [StoreController::class, 'index']);
    Route::post('/stores', [StoreController::class, 'store']);
    Route::get('/stores/{id}', [StoreController::class, 'show']);
    Route::put('/stores/{id}', [StoreController::class, 'update']);
    Route::delete('/stores/{id}', [StoreController::class, 'destroy']);

    // Orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::get('/orders-filter', [OrderController::class, 'filterOrders']);
    Route::get('/orders-stats', [OrderController::class, 'getOrderStats']);
    Route::get('/orders-recent', [OrderController::class, 'getRecentOrders']);
    Route::get('/orders/{id}/details', [OrderController::class, 'getOrderDetails']);

    // Order Updates
    Route::get('/orders/{id}/updates', [OrderController::class, 'getUpdates']);
    Route::post('/orders/{id}/updates', [OrderController::class, 'addUpdate']);
    Route::put('/orders/{id}/updates/{update_id}', [OrderController::class, 'updateUpdate']);
    Route::delete('/orders/{id}/updates/{update_id}', [OrderController::class, 'deleteUpdate']);

    // Order Messages
    Route::get('/orders/{id}/messages', [OrderController::class, 'getMessages']);
    Route::post('/orders/{id}/messages', [OrderController::class, 'addMessage']);
    Route::delete('/orders/{id}/messages/{message_id}', [OrderController::class, 'deleteMessage']);

    // Order Documents
    Route::get('/orders/{id}/documents', [OrderController::class, 'getDocuments']);
    Route::post('/orders/{id}/documents', [OrderController::class, 'uploadDocument']);
    Route::delete('/orders/{id}/documents/{document_id}', [OrderController::class, 'deleteDocument']);

    // User
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/user/update', [UserController::class, 'updateUser']);
});
