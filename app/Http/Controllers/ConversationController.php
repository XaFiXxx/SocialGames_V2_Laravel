<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function createDirect(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $authUser = $request->user();
        $otherUserId = (int) $request->user_id;

        if ((int) $authUser->id === $otherUserId) {
            return response()->json([
                'message' => 'Impossible de créer une conversation avec soi-même.',
            ], 422);
        }

        $existingConversation = DB::table('conversation_user as cu1')
            ->join('conversation_user as cu2', 'cu1.conversation_id', '=', 'cu2.conversation_id')
            ->join('conversations', 'conversations.id', '=', 'cu1.conversation_id')
            ->where('conversations.type', 'direct')
            ->where('cu1.user_id', $authUser->id)
            ->where('cu2.user_id', $otherUserId)
            ->select('cu1.conversation_id')
            ->first();

        if ($existingConversation) {
            return response()->json([
                'conversation_id' => $existingConversation->conversation_id,
            ]);
        }

        $conversation = Conversation::create([
            'type' => 'direct',
            'created_by' => $authUser->id,
        ]);

        $conversation->users()->attach([
            $authUser->id => [
                'role' => 'member',
                'joined_at' => now(),
            ],
            $otherUserId => [
                'role' => 'member',
                'joined_at' => now(),
            ],
        ]);

        return response()->json([
            'conversation_id' => $conversation->id,
        ]);
    }

    public function createGroup(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $authUser = $request->user();

        $conversation = Conversation::create([
            'type' => 'group',
            'name' => $request->name,
            'created_by' => $authUser->id,
        ]);

        $participants = collect($request->user_ids)
            ->push($authUser->id)
            ->unique();

        $attachData = [];

        foreach ($participants as $userId) {
            $attachData[$userId] = [
                'role' => (int) $userId === (int) $authUser->id ? 'admin' : 'member',
                'joined_at' => now(),
            ];
        }

        $conversation->users()->attach($attachData);

        return response()->json($conversation->load('users:id,username,name,surname,avatar_url'));
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = $user->conversations()
            ->with([
                'users:id,username,name,surname,avatar_url',
                'messages' => function ($query) {
                    $query->latest()->limit(1)->with('user:id,username,name,surname,avatar_url');
                },
            ])
            ->latest('updated_at')
            ->get()
            ->map(function ($conversation) {
                $conversation->last_message = $conversation->messages->first();
                unset($conversation->messages);
                return $conversation;
            })
            ->values();

        return response()->json($conversations);
    }
}