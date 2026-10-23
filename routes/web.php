<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\TransmittalController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/audit-trail', [AuditLogController::class, 'index'])->name('audit.index');
    Route::resource('users', UserManagementController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('transmittals', TransmittalController::class)->except(['create']);
    Route::get('/transmittals/create/new', [TransmittalController::class, 'create'])->name('transmittals.create');
    Route::patch('/transmittals/{transmittal}/status', [TransmittalController::class, 'updateStatus'])->name('transmittals.status');
    Route::get('/transmittals/{transmittal}/print', [TransmittalController::class, 'print'])->name('transmittals.print');
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/{item}/history', [ItemController::class, 'history'])->name('items.history');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::patch('/items/{item}/restore', [ItemController::class, 'restore'])->name('items.restore');
});
