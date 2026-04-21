<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\PostReactionController;
use App\Http\Controllers\PostCommentController;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminPostController;
use App\Http\Controllers\Admin\AdminGameController;
use App\Http\Controllers\Admin\AdminPlatformController;
use App\Http\Controllers\Admin\AdminGenreController;

Route::middleware('auth:sanctum')->group(function () {
    // USER
    Route::get('/user', [UserController::class, 'user']);
    Route::get('/profile', [UserController::class, 'me']);
    Route::put('/profile/update', [UserController::class, 'updateProfile']);
    Route::post('/profile/avatar', [UserController::class, 'updateAvatar']);
    Route::post('/profile/cover', [UserController::class, 'updateCover']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
    ->middleware('throttle:2,1');

    // Routes des jeux
    Route::get('/games', [GameController::class, 'index']);

    // Routes des plateformes
    Route::get('/platforms', [PlatformController::class, 'index']);
    Route::get('/platforms/{platform}', [PlatformController::class, 'show']);

    // Barre de recherche
    Route::get('/search/users', [SearchController::class, 'searchUsers']);

    // PUBLIC PROFILE
    Route::get('/user/{id}', [UserController::class, 'show']);

    // NOTIFICATIONS
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAllNotifications']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'readNotification']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);

    // Messages
    Route::get('/conversations/{id}/messages', [MessageController::class, 'getMessages']);

    // Publications
    Route::get('/posts', [PostController::class, 'index']);

    // ADMIN
    Route::middleware('admin')
        ->prefix('admin')
        ->group(function () {
            Route::get('/stats', [AdminDashboardController::class, 'stats']);

            Route::get('/users', [AdminUserController::class, 'index']);
            Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
            Route::patch('/users/{id}/toggle-admin', [AdminUserController::class, 'toggleAdmin']);

            Route::get('/posts', [AdminPostController::class, 'index']);
            Route::delete('/posts/{id}', [AdminPostController::class, 'destroy']);

            Route::get('/games', [AdminGameController::class, 'index']);
            Route::post('/games', [AdminGameController::class, 'store']);
            Route::put('/games/{id}', [AdminGameController::class, 'update']);
            Route::delete('/games/{id}', [AdminGameController::class, 'destroy']);

            Route::get('/platforms', [AdminPlatformController::class, 'index']);
            Route::post('/platforms', [AdminPlatformController::class, 'store']);
            Route::put('/platforms/{id}', [AdminPlatformController::class, 'update']);
            Route::delete('/platforms/{id}', [AdminPlatformController::class, 'destroy']);

            Route::get('/genres', [AdminGenreController::class, 'index']);
            Route::post('/genres', [AdminGenreController::class, 'store']);
            Route::put('/genres/{id}', [AdminGenreController::class, 'update']);
            Route::delete('/genres/{id}', [AdminGenreController::class, 'destroy']);
        });
});

Route::middleware(['auth:sanctum', 'verified.json'])->group(function () {
    // Routes des jeux
    Route::post('/user/games', [GameController::class, 'store']);
    Route::patch('/games/{game}', [GameController::class, 'update']);
    Route::delete('/games/{game}', [GameController::class, 'destroy']);

    // Routes des plateformes
    Route::post('/user/platforms', [PlatformController::class, 'store']);
    Route::patch('/user/platforms/{platform}', [PlatformController::class, 'update']);
    Route::delete('/user/platforms/{platform}', [PlatformController::class, 'destroy']);

    // FOLLOW
    Route::post('/user/{id}/follow', [UserController::class, 'followUser']);
    Route::delete('/user/{id}/follow', [UserController::class, 'unfollowUser']);

    // FRIENDS
    Route::post('/user/{id}/friend-request', [UserController::class, 'sendFriendRequest']);
    Route::post('/user/{id}/friend-accept', [UserController::class, 'acceptFriendRequest']);
    Route::delete('/user/{id}/friendship', [UserController::class, 'removeFriendship']);

    // Conversations
    Route::post('/conversations/direct', [ConversationController::class, 'createDirect']);
    Route::post('/conversations/group', [ConversationController::class, 'createGroup']);

    // Messages
    Route::post('/conversations/{id}/messages', [MessageController::class, 'sendMessage']);
    Route::post('/conversations/{conversationId}/read', [MessageController::class, 'markConversationAsRead']);

    // Publications
    Route::post('/posts', [PostController::class, 'store']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    Route::post('/posts/{post}/react', [PostReactionController::class, 'toggle']);
    Route::get('/posts/{post}/comments', [PostCommentController::class, 'index']);
    Route::post('/posts/{post}/comments', [PostCommentController::class, 'store']);
    Route::delete('/comments/{comment}', [PostCommentController::class, 'destroy']);
});