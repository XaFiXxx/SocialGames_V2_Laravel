<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    public function index(Request $request, int $post): JsonResponse
    {
        Post::findOrFail($post);

        $comments = PostComment::query()
            ->with([
                'user:id,username,name,surname,avatar_url',
            ])
            ->where('post_id', $post)
            ->whereNull('parent_id')
            ->latest()
            ->get([
                'id',
                'post_id',
                'user_id',
                'parent_id',
                'content',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->map(function ($comment) use ($request) {
                return [
                    'id' => $comment->id,
                    'post_id' => $comment->post_id,
                    'user_id' => $comment->user_id,
                    'parent_id' => $comment->parent_id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at,
                    'updated_at' => $comment->updated_at,
                    'deleted_at' => $comment->deleted_at,
                    'user' => $comment->user,
                    'is_owner' => (int) $comment->user_id === (int) $request->user()->id,
                ];
            })
            ->values();

        return response()->json($comments);
    }

    public function store(Request $request, int $post): JsonResponse
    {
        $user = $request->user();

        Post::findOrFail($post);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:post_comments,id'],
        ]);

        if (!empty($validated['parent_id'])) {
            $parentComment = PostComment::findOrFail($validated['parent_id']);

            if ((int) $parentComment->post_id !== (int) $post) {
                return response()->json([
                    'message' => 'Le commentaire parent ne correspond pas à ce post.',
                ], 422);
            }
        }

        $comment = PostComment::create([
            'post_id' => $post,
            'user_id' => $user->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => trim($validated['content']),
        ]);

        $comment->load('user:id,username,name,surname,avatar_url');

        return response()->json([
            'message' => 'Commentaire ajouté avec succès.',
            'comment' => [
                'id' => $comment->id,
                'post_id' => $comment->post_id,
                'user_id' => $comment->user_id,
                'parent_id' => $comment->parent_id,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
                'deleted_at' => $comment->deleted_at,
                'user' => $comment->user,
                'is_owner' => true,
            ],
        ], 201);
    }

    public function destroy(Request $request, int $comment): JsonResponse
    {
        $user = $request->user();

        $commentModel = PostComment::findOrFail($comment);

        if ((int) $commentModel->user_id !== (int) $user->id) {
            return response()->json([
                'message' => 'Vous n’êtes pas autorisé à supprimer ce commentaire.',
            ], 403);
        }

        $commentModel->delete();

        return response()->json([
            'message' => 'Commentaire supprimé avec succès.',
        ]);
    }
}