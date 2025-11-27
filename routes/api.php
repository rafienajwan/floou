<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PlantTypeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\DashboardController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public plant catalog routes
Route::get('/plants', [PlantController::class, 'index']);
Route::get('/plants/{plant}', [PlantController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/plant-types', [PlantTypeController::class, 'index']);

// Public reviews
Route::get('/plants/{plant}/reviews', [ReviewController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // User orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/{id}/invoice', [OrderController::class, 'getInvoice']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::get('/my-reviews', [ReviewController::class, 'userReviews']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Admin order management
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    // Categories management
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Plant types management
    Route::post('/plant-types', [PlantTypeController::class, 'store']);
    Route::put('/plant-types/{plantType}', [PlantTypeController::class, 'update']);
    Route::delete('/plant-types/{plantType}', [PlantTypeController::class, 'destroy']);

    // Plants management
    Route::post('/plants', [PlantController::class, 'store']);
    Route::post('/plants/{plant}', [PlantController::class, 'update']);
    Route::delete('/plants/{plant}', [PlantController::class, 'destroy']);
});

// Admin User Management routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Kelola Akun
    Route::get('/users', [UserManagementController::class, 'index']);
    Route::get('/users/{id}', [UserManagementController::class, 'show']);
    Route::put('/users/{id}', [UserManagementController::class, 'update']);
    Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);

    // Kelola Pesanan (Admin)
    Route::get('/orders', [OrderController::class, 'adminIndex']);
    Route::put('/orders/{id}', [OrderController::class, 'updateOrderDetails']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
});
