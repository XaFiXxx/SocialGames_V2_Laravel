<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class AdminPlatformController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $platforms = Platform::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return response()->json($platforms);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:platforms,name'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $platform = Platform::create([
            'name' => $data['name'],
            'slug' => $this->makeUniqueSlug($data['name']),
            'logo' => null,
        ]);

        if ($request->hasFile('logo')) {
            $this->savePlatformLogo($platform, $request->file('logo'));
        }

        return response()->json([
            'message' => 'Plateforme créée avec succès.',
            'platform' => $platform->fresh(),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $platform = Platform::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:platforms,name,' . $platform->id],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $platform->update([
            'name' => $data['name'],
            'slug' => $this->makeUniqueSlug($data['name'], $platform->id),
        ]);

        if ($request->hasFile('logo')) {
            $this->savePlatformLogo($platform, $request->file('logo'));
        }

        return response()->json([
            'message' => 'Plateforme mise à jour.',
            'platform' => $platform->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $platform = Platform::findOrFail($id);

        $logoDirectory = public_path("storage/img/platforms/{$platform->id}");
        if (File::exists($logoDirectory)) {
            File::deleteDirectory($logoDirectory);
        }

        $platform->delete();

        return response()->json([
            'message' => 'Plateforme supprimée.',
        ]);
    }

    private function savePlatformLogo(Platform $platform, $file): void
    {
        $destinationPath = public_path("storage/img/platforms/{$platform->id}");

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        File::cleanDirectory($destinationPath);

        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getRealPath());

        $image->cover(256, 256);

        $fileName = 'logo.webp';
        $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $fileName;

        $image->toWebp(80)->save($fullPath);

        $platform->logo = "storage/img/platforms/{$platform->id}/{$fileName}";
        $platform->save();
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : 'platform';
        $originalSlug = $slug;
        $count = 1;

        while (
            Platform::query()
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