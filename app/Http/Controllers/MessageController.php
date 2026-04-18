<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MessageController extends Controller
{
    public function sendMessage(Request $request, int $conversationId): JsonResponse
    {
        $request->validate([
            'content' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $user = $request->user();

        $conversation = Conversation::findOrFail($conversationId);

        if (!$conversation->users()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'Non autorisé.',
            ], 403);
        }

        $content = trim((string) $request->input('content', ''));
        $imagePath = null;

        if ($request->hasFile('image')) {
            $destinationPath = public_path("storage/img/messages/{$conversation->id}");

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $manager = ImageManager::usingDriver(Driver::class);
            $image = $manager->decodeSplFileInfo($request->file('image'));

            if ($image->width() > 1600) {
                $image->scale(width: 1600);
            }

            $fileName = time() . '_' . uniqid() . '.webp';
            $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $fileName;

            $image->save($fullPath, quality: 85);

            $imagePath = "storage/img/messages/{$conversation->id}/{$fileName}";
        }

        if ($content === '' && !$imagePath) {
            return response()->json([
                'message' => 'Le message ne peut pas être vide.',
            ], 422);
        }

        $type = 'text';

        if ($imagePath && $content !== '') {
            $type = 'mixed';
        } elseif ($imagePath) {
            $type = 'image';
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'type' => $type,
            'content' => $content !== '' ? $content : null,
            'image_url' => $imagePath,
        ]);

        $conversation->touch();

        event(new MessageSent($message));

        return response()->json(
            $message->load('user:id,username,name,surname,avatar_url')
        );
    }

    public function getMessages(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();

        $conversation = Conversation::findOrFail($conversationId);

        if (!$conversation->users()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'Non autorisé.',
            ], 403);
        }

        $messages = $conversation->messages()
            ->with('user:id,username,name,surname,avatar_url')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }
}