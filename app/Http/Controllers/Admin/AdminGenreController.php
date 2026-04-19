<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminGenreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $genres = Genre::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return response()->json($genres);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:genres,name'],
        ]);

        $genre = Genre::create([
            'name' => $data['name'],
            'slug' => $this->makeUniqueSlug($data['name']),
        ]);

        return response()->json([
            'message' => 'Genre créé avec succès.',
            'genre' => $genre,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $genre = Genre::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:genres,name,' . $genre->id],
        ]);

        $genre->update([
            'name' => $data['name'],
            'slug' => $this->makeUniqueSlug($data['name'], $genre->id),
        ]);

        return response()->json([
            'message' => 'Genre mis à jour.',
            'genre' => $genre->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $genre = Genre::findOrFail($id);

        $genre->delete();

        return response()->json([
            'message' => 'Genre supprimé.',
        ]);
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : 'genre';
        $originalSlug = $slug;
        $count = 1;

        while (
            Genre::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}