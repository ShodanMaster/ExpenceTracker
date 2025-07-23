<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReccuringTransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('expenses', ExpenseController::class);
    Route::get('get-expenses/{date}', [ExpenseController::class, 'getExpenses'])->name('getexpenses');

    Route::resource('reccuring-transactions', ReccuringTransactionController::class);
    Route::get('/transactions', [ReccuringTransactionController::class, 'getTransactions'])->name('reccuring-transactions.fetch');
    Route::post('activate-reccuring-transaction', [ReccuringTransactionController::class, 'activate'])->name('reccuring-transactions.activate');
});

require __DIR__.'/auth.php';
