<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class UserController extends Controller
{
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'birthday' => ['required', 'date', 'before:today'],
            'biography' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:100'],
            'newsletter' => ['required', 'boolean'],
        ]);

        $user->update($validated);

        return response()->json($user->fresh());
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = $request->user();
        $destinationPath = public_path("storage/img/users/{$user->id}/avatar");

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        File::cleanDirectory($destinationPath);

        $manager = ImageManager::usingDriver(Driver::class);
        $image = $manager->decodeSplFileInfo($request->file('avatar'));

        $image->cover(300, 300);

        $fileName = 'avatar.webp';
        $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $fileName;

        $image->save($fullPath, quality: 80);

        $user->avatar_url = "storage/img/users/{$user->id}/avatar/{$fileName}";
        $user->save();

        return response()->json([
            'message' => 'Avatar mis à jour avec succès.',
            'user' => $user->fresh(),
        ]);
    }

    public function updateCover(Request $request): JsonResponse
    {
        $request->validate([
            'cover' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $user = $request->user();
        $destinationPath = public_path("storage/img/users/{$user->id}/cover");

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        File::cleanDirectory($destinationPath);

        $manager = ImageManager::usingDriver(Driver::class);
        $image = $manager->decodeSplFileInfo($request->file('cover'));

        // Redimensionne sans couper, largeur max 1600px
        if ($image->width() > 1600) {
            $image->scale(width: 1600);
        }

        $fileName = 'cover.webp';
        $fullPath = $destinationPath . DIRECTORY_SEPARATOR . $fileName;

        $image->save($fullPath, quality: 85);

        $user->cover_url = "storage/img/users/{$user->id}/cover/{$fileName}";
        $user->save();

        return response()->json([
            'message' => 'Cover mise à jour avec succès.',
            'user' => $user->fresh(),
        ]);
    }
}