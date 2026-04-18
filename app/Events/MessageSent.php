<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load('user:id,username,name,surname,avatar_url');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->conversation_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'user_id' => $this->message->user_id,
            'type' => $this->message->type,
            'content' => $this->message->content,
            'image_url' => $this->message->image_url,
            'created_at' => $this->message->created_at,
            'updated_at' => $this->message->updated_at,
            'user' => [
                'id' => $this->message->user?->id,
                'username' => $this->message->user?->username,
                'name' => $this->message->user?->name,
                'surname' => $this->message->user?->surname,
                'avatar_url' => $this->message->user?->avatar_url,
            ],
        ];
    }
}