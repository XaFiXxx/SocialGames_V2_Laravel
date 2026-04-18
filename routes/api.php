<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // USER
    Route::get('/user', [UserController::class, 'user']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::post('/user/avatar', [UserController::class, 'updateAvatar']);
    Route::post('/user/cover', [UserController::class, 'updateCover']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // PUBLIC PROFILE
    Route::get('/user/{id}', [UserController::class, 'show']);

    // FOLLOW
    Route::post('/user/{id}/follow', [UserController::class, 'followUser']);
    Route::delete('/user/{id}/follow', [UserController::class, 'unfollowUser']);

    // FRIENDS
    Route::post('/user/{id}/friend-request', [UserController::class, 'sendFriendRequest']);
    Route::post('/user/{id}/friend-accept', [UserController::class, 'acceptFriendRequest']);
    Route::delete('/user/{id}/friendship', [UserController::class, 'removeFriendship']);

    // 🔔 NOTIFICATIONS
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAllNotifications']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'readNotification']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});