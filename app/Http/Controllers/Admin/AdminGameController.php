<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AdminGameController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $games = Game::query()
            ->with(['platforms:id,name,slug,logo', 'genres:id,name,slug'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('developer', 'like', "%{$search}%")
                        ->orWhere('publisher', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return response()->json($games);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'release_at' => ['nullable', 'date'],
            'developer' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'platform_ids' => ['nullable', 'array'],
            'platform_ids.*' => ['integer', 'exists:platforms,id'],

            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
        ]);

        $game = Game::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'release_at' => $data['release_at'] ?? null,
            'developer' => $data['developer'] ?? null,
            'publisher' => $data['publisher'] ?? null,
            'cover_img' => null,
        ]);

        if ($request->hasFile('cover')) {
            $this->saveGameCover($game, $request->file('cover'));
        }

        $game->platforms()->sync($data['platform_ids'] ?? []);
        $game->genres()->sync($data['genre_ids'] ?? []);

        return response()->json([
            'message' => 'Jeu créé avec succès.',
            'game' => $game->load([
                'platforms:id,name,slug,logo',
                'genres:id,name,slug',
            ]),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $game = Game::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'release_at' => ['nullable', 'date'],
            'developer' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'platform_ids' => ['nullable', 'array'],
            'platform_ids.*' => ['integer', 'exists:platforms,id'],

            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
        ]);

        $game->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'release_at' => $data['release_at'] ?? null,
            'developer' => $data['developer'] ?? null,
            'publisher' => $data['publisher'] ?? null,
        ]);

        if ($request->hasFile('cover')) {
            $this->saveGameCover($game, $request->file('cover'));
        }

        $game->platforms()->sync($data['platform_ids'] ?? []);
        $game->genres()->sync($data['genre_ids'] ?? []);

        return response()->json([
            'message' => 'Jeu mis à jour.',
            'game' => $game->load([
                'platforms:id,name,slug,logo',
                'genres:id,name,slug',
            ]),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $game = Game::findOrFail($id);

        $coverDirectory = public_path("storage/img/games/{$game->id}");
        if (File::exists($coverDirectory)) {
            File::deleteDirectory($coverDirectory);
        }

        $game->delete();

        return response()->json([
            'message' => 'Jeu supprimé.',
        ]);
    }

    private function saveGameCover(Game $game, $file): void
    {
        $destinationPath = public_path("storage/img/games/{$game->id}");

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        File::cleanDirectory($destinationPath);

        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());

        $image->cover(600, 800);

        $fileName = 'cover.webp';
        $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $fileName;

        $image->toWebp(80)->save($fullPath);

        $game->cover_img = "storage/img/games/{$game->id}/{$fileName}";
        $game->save();
    }
}