<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifikatorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [AdminController::class, 'listUsers']);
        Route::post('/users/verifikator/create', [AdminController::class, 'createVerifikator']);
        Route::put('/users/{id}/to-verifikator', [AdminController::class, 'promoteToVerifikator']);
        Route::put('/users/{id}/reset-password', [AdminController::class, 'resetPassword']);
        Route::get('/users/permissions', [AdminController::class, 'listPermissions']);
    });

    Route::middleware('role:verifikator')->group(function () {
        Route::post('/users/filter', [VerifikatorController::class, 'filterUsers']);
        Route::put('/users/{id}/verify', [VerifikatorController::class, 'verifyUser']);
        Route::post('/permissions/filter', [VerifikatorController::class, 'filterPermissions']);
        Route::put('/permissions/{id}/status', [VerifikatorController::class, 'updatePermissionStatus']);
    });

    Route::middleware('role:user')->group(function () {
        Route::post('/permissions/create', [UserController::class, 'store']);
        Route::get('/permissions', [UserController::class, 'index']);
        Route::put('/permissions/{id}', [UserController::class, 'update']);
        Route::delete('/permissions/{id}', [UserController::class, 'destroy']);
        Route::put('/permissions/{id}/cancel', [UserController::class, 'cancel']);
        Route::put('/users/update-password', [UserController::class, 'updatePassword']);
    });
});
