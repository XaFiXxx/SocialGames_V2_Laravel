<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesSeen implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public int $conversationId;
    public array $messageIds;
    public string $seenAt;

    public function __construct(int $conversationId, array $messageIds, string $seenAt)
    {
        $this->conversationId = $conversationId;
        $this->messageIds = $messageIds;
        $this->seenAt = $seenAt;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'messages.seen';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'message_ids' => $this->messageIds,
            'seen_at' => $this->seenAt,
        ];
    }
}