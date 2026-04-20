<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    /**
     * Catalogue des plateformes
     */
    public function index(): JsonResponse
    {
        $platforms = Platform::query()
            ->select('id', 'name', 'logo', 'slug')
            ->orderBy('name')
            ->get();

        return response()->json($platforms);
    }

    /**
     * Ajouter une plateforme au profil connecté
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'platform_id' => ['required', 'exists:platforms,id'],
        ]);

        $alreadyAttached = $user->platforms()
            ->where('platform_id', $validated['platform_id'])
            ->exists();

        if ($alreadyAttached) {
            return response()->json([
                'message' => 'Cette plateforme est déjà ajoutée à ton profil.',
            ], 422);
        }

        $user->platforms()->attach($validated['platform_id']);

        $platform = $user->platforms()
            ->where('platforms.id', $validated['platform_id'])
            ->first();

        return response()->json([
            'message' => 'Plateforme ajoutée au profil avec succès.',
            'platform' => $platform,
        ], 201);
    }

    /**
     * Afficher une plateforme du catalogue
     */
    public function show(Platform $platform): JsonResponse
    {
        return response()->json($platform);
    }

    /**
     * Mettre à jour une plateforme du profil connecté
     * Pour l’instant il n’y a rien à modifier sur la pivot,
     * mais on garde la route prête.
     */
    public function update(Request $request, Platform $platform): JsonResponse
    {
        $user = $request->user();

        $attached = $user->platforms()
            ->where('platform_id', $platform->id)
            ->exists();

        if (! $attached) {
            return response()->json([
                'message' => 'Cette plateforme n’est pas liée à ton profil.',
            ], 404);
        }

        return response()->json([
            'message' => 'Aucune donnée à mettre à jour pour cette plateforme.',
            'platform' => $platform,
        ]);
    }

    /**
     * Supprimer une plateforme du profil connecté
     */
    public function destroy(Request $request, Platform $platform): JsonResponse
    {
        $user = $request->user();

        $attached = $user->platforms()
            ->where('platform_id', $platform->id)
            ->exists();

        if (! $attached) {
            return response()->json([
                'message' => 'Cette plateforme n’est pas liée à ton profil.',
            ], 404);
        }

        $user->platforms()->detach($platform->id);

        return response()->json([
            'message' => 'Plateforme retirée du profil avec succès.',
        ]);
    }
}