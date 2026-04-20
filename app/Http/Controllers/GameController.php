<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Liste tous les jeux (pour le select côté front)
     */
    public function index(): JsonResponse
    {
        $games = Game::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'developer',
                'publisher',
                'release_at',
                'cover_img',
            ]);

        return response()->json($games);
    }

    /**
     * Ajouter un jeu au profil du user
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'game_id' => ['required', 'exists:games,id'],
            'skill_level' => ['nullable', 'string', 'max:255'],
            'favorite' => ['required', 'boolean'],
        ]);

        // empêcher doublon
        $alreadyExists = $user->games()
            ->where('games.id', $validated['game_id'])
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'message' => 'Ce jeu est déjà dans ton profil.',
            ], 422);
        }

        // gérer favori unique
        if ($validated['favorite']) {
            foreach ($user->games as $existingGame) {
                $user->games()->updateExistingPivot($existingGame->id, [
                    'favorite' => false,
                ]);
            }
        }

        $user->games()->attach($validated['game_id'], [
            'skill_level' => $validated['skill_level'] ?? null,
            'favorite' => $validated['favorite'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Jeu ajouté au profil.',
        ], 201);
    }

    /**
     * Afficher un jeu (optionnel)
     */
    public function show(Game $game): JsonResponse
    {
        return response()->json($game);
    }

    /**
     * Update skill_level / favorite d’un jeu user
     */
    public function update(Request $request, Game $game): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'skill_level' => ['nullable', 'string', 'max:255'],
            'favorite' => ['required', 'boolean'],
        ]);

        $exists = $user->games()
            ->where('games.id', $game->id)
            ->exists();

        if (!$exists) {
            return response()->json([
                'message' => 'Jeu non présent dans ton profil.',
            ], 404);
        }

        // gérer favori unique
        if ($validated['favorite']) {
            foreach ($user->games as $existingGame) {
                $user->games()->updateExistingPivot($existingGame->id, [
                    'favorite' => false,
                ]);
            }
        }

        $user->games()->updateExistingPivot($game->id, [
            'skill_level' => $validated['skill_level'] ?? null,
            'favorite' => $validated['favorite'],
        ]);

        return response()->json([
            'message' => 'Jeu mis à jour.',
        ]);
    }

    /**
     * Retirer un jeu du profil
     */
    public function destroy(Request $request, Game $game): JsonResponse
    {
        $user = $request->user();

        $exists = $user->games()
            ->where('games.id', $game->id)
            ->exists();

        if (!$exists) {
            return response()->json([
                'message' => 'Jeu non présent dans ton profil.',
            ], 404);
        }

        $user->games()->detach($game->id);

        return response()->json([
            'message' => 'Jeu supprimé du profil.',
        ]);
    }
}