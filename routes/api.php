<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->get('/transactions/stats', [TransactionController::class, 'stats']);

Route::middleware(['auth:sanctum'])->apiResource('transactions', TransactionController::class);