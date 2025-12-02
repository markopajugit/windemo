<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LotteryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Stripe webhook (no auth required)
Route::post('/webhook/stripe', [PaymentController::class, 'handleWebhook']);

// Public lottery routes
Route::get('/lotteries', [LotteryController::class, 'index']);
Route::get('/lotteries/categories', [LotteryController::class, 'categories']);
Route::get('/lotteries/popular', [LotteryController::class, 'popular']);
Route::get('/lotteries/upcoming', [LotteryController::class, 'upcoming']);
Route::get('/lotteries/ended', [LotteryController::class, 'ended']);
Route::get('/lotteries/{lottery}', [LotteryController::class, 'show'])->where('lottery', '[0-9]+');

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification']);
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

    // User lotteries
    Route::get('/user/lotteries', [LotteryController::class, 'userLotteries']);
    Route::post('/user/lotteries', [LotteryController::class, 'store'])->middleware('verified');
    Route::post('/user/lotteries/{lottery}', [LotteryController::class, 'update'])->middleware('verified')->where('lottery', '[0-9]+');
    Route::delete('/user/lotteries/{lottery}', [LotteryController::class, 'destroy'])->where('lottery', '[0-9]+');

    // Tickets
    Route::post('/lotteries/{lottery}/tickets', [TicketController::class, 'purchase'])->middleware('verified')->where('lottery', '[0-9]+');
    Route::get('/user/tickets', [TicketController::class, 'userTickets']);

    // Stripe checkout
    Route::post('/checkout/{lottery}/session', [PaymentController::class, 'createCheckoutSession'])->middleware('verified')->where('lottery', '[0-9]+');
    Route::get('/checkout/verify', [PaymentController::class, 'verifySession']);

    // Admin routes
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/lotteries', [AdminController::class, 'lotteries']);
        Route::put('/lotteries/{lottery}/approve', [AdminController::class, 'approve'])->where('lottery', '[0-9]+');
        Route::put('/lotteries/{lottery}/reject', [AdminController::class, 'reject'])->where('lottery', '[0-9]+');
        Route::put('/lotteries/{lottery}', [AdminController::class, 'update'])->where('lottery', '[0-9]+');
    });
});

