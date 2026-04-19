<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $posts = Post::query()
            ->with('user:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('content', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return response()->json($posts);
    }

    public function destroy(int $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return response()->json([
            'message' => 'Post supprimé avec succès.',
        ]);
    }
}