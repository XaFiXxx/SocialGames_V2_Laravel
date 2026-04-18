<?php

use App\Models\Conversation;

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    return $conversation->users()
        ->where('user_id', $user->id)
        ->exists();
});

Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('messages-notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});