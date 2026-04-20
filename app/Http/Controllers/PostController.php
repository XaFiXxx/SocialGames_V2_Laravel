<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $posts = Post::with([
                'user',
                'media',
                'reactions:id,post_id,user_id,type',
            ])
            ->where('is_active', true)
            ->where('visibility', 'public')
            ->latest()
            ->paginate(10);

        $posts->getCollection()->transform(function ($post) use ($authUser) {
            $reactionCounts = $post->reactions
                ->groupBy('type')
                ->map(fn ($items) => $items->count());

            $post->reactions_count = [
                'like' => $reactionCounts->get('like', 0),
                'fire' => $reactionCounts->get('fire', 0),
                'gg' => $reactionCounts->get('gg', 0),
                'total' => $post->reactions->count(),
            ];

            $post->user_reaction = $authUser
                ? optional($post->reactions->firstWhere('user_id', $authUser->id))->type
                : null;

            $post->is_owner = $authUser
                ? (int) $post->user_id === (int) $authUser->id
                : false;

            unset($post->reactions);

            return $post;
        });

        return response()->json($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'content' => ['nullable', 'string', 'max:2000'],
            'visibility' => ['required', 'in:public,friends,private'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'media' => ['nullable', 'array', 'max:6'],
            'media.*' => ['file', 'mimes:jpg,jpeg,png,webp,mp4,webm,mov', 'max:20480'],
        ]);

        $hasContent = !empty(trim($validated['content'] ?? ''));
        $hasMedia = $request->hasFile('media');

        if (! $hasContent && ! $hasMedia) {
            return response()->json([
                'message' => 'Le post doit contenir un texte ou au moins un média.'
            ], 422);
        }

        if ($request->hasFile('media')) {
            $videoCount = 0;

            foreach ($request->file('media') as $file) {
                if (str_starts_with($file->getMimeType(), 'video/')) {
                    $videoCount++;
                }
            }

            if ($videoCount > 1) {
                return response()->json([
                    'message' => 'Une seule vidéo est autorisée par post.'
                ], 422);
            }
        }

        $post = Post::create([
            'user_id' => $user->id,
            'content' => $validated['content'] ?? null,
            'visibility' => $validated['visibility'],
            'group_id' => $validated['group_id'] ?? null,
            'team_id' => $validated['team_id'] ?? null,
        ]);

        if ($request->hasFile('media')) {
            $destinationPath = public_path("storage/img/posts/{$post->id}");

            if (! File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $manager = new ImageManager(new Driver());

            foreach ($request->file('media') as $index => $file) {
                $mimeType = $file->getMimeType();

                if (str_starts_with($mimeType, 'image/')) {
                    $fileName = 'media_' . ($index + 1) . '.webp';
                    $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $fileName;

                    $image = $manager->read($file->getRealPath());
                    $image->scaleDown(width: 1600);
                    $image->toWebp(80)->save($fullPath);

                    PostMedia::create([
                        'post_id' => $post->id,
                        'type' => 'image',
                        'url' => "storage/img/posts/{$post->id}/{$fileName}",
                        'position' => $index,
                    ]);
                } elseif (str_starts_with($mimeType, 'video/')) {
                    $extension = strtolower($file->getClientOriginalExtension()) ?: 'mp4';
                    $fileName = 'media_' . ($index + 1) . '.' . $extension;

                    $file->move($destinationPath, $fileName);

                    PostMedia::create([
                        'post_id' => $post->id,
                        'type' => 'video',
                        'url' => "storage/img/posts/{$post->id}/{$fileName}",
                        'position' => $index,
                    ]);
                }
            }
        }

        $post->load(['user', 'media', 'reactions']);

        $post->reactions_count = [
            'like' => 0,
            'fire' => 0,
            'gg' => 0,
            'total' => 0,
        ];

        $post->user_reaction = null;
        $post->is_owner = true;

        unset($post->reactions);

        return response()->json([
            'message' => 'Post créé avec succès',
            'post' => $post,
        ], 201);
    }

    public function destroy(Request $request, int $postId): JsonResponse
    {
        $user = $request->user();

        $post = Post::with('media')->findOrFail($postId);

        if ((int) $post->user_id !== (int) $user->id) {
            return response()->json([
                'message' => 'Vous n’êtes pas autorisé à supprimer ce post.',
            ], 403);
        }

        $postFolderPath = public_path("storage/img/posts/{$post->id}");

        if (File::exists($postFolderPath)) {
            File::deleteDirectory($postFolderPath);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post supprimé avec succès.',
        ]);
    }
}