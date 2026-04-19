<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Friend;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return response()->json($users);
    }

    public function toggleAdmin(Request $request, int $id): JsonResponse
    {
        $targetUser = User::findOrFail($id);

        if ($request->user()->id === $targetUser->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas modifier votre propre rôle admin.'
            ], 422);
        }

        $targetUser->is_admin = !$targetUser->is_admin;
        $targetUser->save();

        return response()->json([
            'message' => 'Statut admin mis à jour.',
            'user' => $targetUser,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $targetUser = User::findOrFail($id);

        if ($request->user()->id === $targetUser->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte admin.'
            ], 422);
        }

        DB::transaction(function () use ($targetUser) {
            $targetUser->games()->detach();
            $targetUser->platforms()->detach();
            $targetUser->groups()->detach();
            $targetUser->teams()->detach();
            $targetUser->conversations()->detach();

            $targetUser->following()->detach();
            $targetUser->followers()->detach();

            Friend::where('sender_id', $targetUser->id)
                ->orWhere('receiver_id', $targetUser->id)
                ->delete();

            $targetUser->posts()->delete();
            $targetUser->messages()->delete();

            $targetUser->delete();
        });

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès.'
        ]);
    }
}