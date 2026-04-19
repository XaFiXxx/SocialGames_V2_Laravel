<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SearchController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // USER
    Route::get('/user', [UserController::class, 'user']);
    Route::get('/profile', [UserController::class, 'me']);
    Route::put('/profile/update', [UserController::class, 'updateProfile']);
    Route::post('/profile/avatar', [UserController::class, 'updateAvatar']);
    Route::post('/profile/cover', [UserController::class, 'updateCover']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Barre de recherche 
    Route::get('/search/users', [SearchController::class, 'searchUsers']);

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

    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations/direct', [ConversationController::class, 'createDirect']);
    Route::post('/conversations/group', [ConversationController::class, 'createGroup']);

    // Messages
    Route::get('/conversations/{id}/messages', [MessageController::class, 'getMessages']);
    Route::post('/conversations/{id}/messages', [MessageController::class, 'sendMessage']);
    Route::post('/conversations/{conversationId}/read', [MessageController::class, 'markConversationAsRead']);
});