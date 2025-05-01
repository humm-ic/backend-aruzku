<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\IncomeController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\SavingController;
use App\Http\Controllers\API\ExpenseController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\SavingsGoalController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\NotificationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // User Profile
    Route::post('/user/profile', [UserController::class, 'updateProfile']);
    Route::post('/user/password', [UserController::class, 'updatePassword']);
    
    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    
    // Expenses
    Route::apiResource('expenses', ExpenseController::class);
    
    // Incomes
    Route::apiResource('incomes', IncomeController::class);
    
    // Savings
    Route::apiResource('savings', SavingController::class);
    
    // Savings Goals
    Route::apiResource('savings-goals', SavingsGoalController::class);
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    
    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    
    // Reports
    Route::get('/reports/monthly', [ReportController::class, 'monthlyReport']);
    Route::get('/reports/pdf', [ReportController::class, 'generatePdf']);
    Route::get('/reports/analysis', [ReportController::class, 'analysis']);
});