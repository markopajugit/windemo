<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LotteryController;
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

// Public lottery routes
Route::get('/lotteries', [LotteryController::class, 'index']);
Route::get('/lotteries/popular', [LotteryController::class, 'popular']);
Route::get('/lotteries/upcoming', [LotteryController::class, 'upcoming']);
Route::get('/lotteries/ended', [LotteryController::class, 'ended']);
Route::get('/lotteries/{lottery}', [LotteryController::class, 'show']);

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
    Route::post('/user/lotteries/{lottery}', [LotteryController::class, 'update'])->middleware('verified');
    Route::delete('/user/lotteries/{lottery}', [LotteryController::class, 'destroy']);

    // Tickets
    Route::post('/lotteries/{lottery}/tickets', [TicketController::class, 'purchase'])->middleware('verified');
    Route::get('/user/tickets', [TicketController::class, 'userTickets']);

    // Admin routes
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/lotteries', [AdminController::class, 'lotteries']);
        Route::put('/lotteries/{lottery}/approve', [AdminController::class, 'approve']);
        Route::put('/lotteries/{lottery}/reject', [AdminController::class, 'reject']);
        Route::put('/lotteries/{lottery}', [AdminController::class, 'update']);
    });
});

