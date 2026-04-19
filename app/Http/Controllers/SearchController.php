<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class SearchController extends Controller
{
    public function searchUsers(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $authUser = $request->user();

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->select('id', 'username', 'name', 'surname', 'avatar_url')
            ->when($authUser, function ($builder) use ($authUser) {
                $builder->where('id', '!=', $authUser->id);
            })
            ->where(function ($builder) use ($query) {
                $builder->where('username', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('surname', 'like', "%{$query}%");
            })
            ->orderByRaw("CASE WHEN username LIKE ? THEN 0 ELSE 1 END", ["{$query}%"])
            ->orderBy('username')
            ->limit(8)
            ->get();

        return response()->json($users);
    }
}