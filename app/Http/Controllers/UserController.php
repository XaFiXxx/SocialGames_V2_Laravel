<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use App\Models\User;
use App\Models\Notification;
use App\Events\NotificationSent; // ✅ AJOUT

class UserController extends Controller
{
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $profile = User::findOrFail($id);
        $authUser = $request->user();

        $isOwnProfile = (int) $authUser->id === (int) $profile->id;

        // FOLLOW
        $isFollowing = DB::table('follows')
            ->where('follower_id', $authUser->id)
            ->where('following_id', $profile->id)
            ->exists();

        // FRIENDSHIP
        $friendship = DB::table('friends')
            ->where(function ($query) use ($authUser, $profile) {
                $query->where('sender_id', $authUser->id)
                    ->where('receiver_id', $profile->id);
            })
            ->orWhere(function ($query) use ($authUser, $profile) {
                $query->where('sender_id', $profile->id)
                    ->where('receiver_id', $authUser->id);
            })
            ->first();

        $friendStatus = $friendship?->status;
        $friendRequestSentByMe = $friendship
            ? (int) $friendship->sender_id === (int) $authUser->id
            : false;

        // COUNTS
        $friendsCount = DB::table('friends')
            ->where(function ($query) use ($profile) {
                $query->where('sender_id', $profile->id)
                    ->orWhere('receiver_id', $profile->id);
            })
            ->where('status', 'accepted')
            ->count();

        $followersCount = DB::table('follows')
            ->where('following_id', $profile->id)
            ->count();

        return response()->json([
            'user' => $profile,
            'meta' => [
                'is_own_profile' => $isOwnProfile,
                'is_following' => $isFollowing,
                'friend_status' => $friendStatus,
                'friend_request_sent_by_me' => $friendRequestSentByMe,
                'friends_count' => $friendsCount,
                'followers_count' => $followersCount,
            ],
        ]);
    }

    // ---------------- Routes profile personnel ----------------

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

    // ---------------- Follow ----------------

    public function followUser(Request $request, int $id): JsonResponse
    {
        $authUser = $request->user();

        if ((int) $authUser->id === (int) $id) {
            return response()->json([
                'message' => 'Tu ne peux pas te suivre toi-même.',
            ], 422);
        }

        $target = User::findOrFail($id);

        $exists = DB::table('follows')
            ->where('follower_id', $authUser->id)
            ->where('following_id', $target->id)
            ->exists();

        if (!$exists) {
            DB::table('follows')->insert([
                'follower_id' => $authUser->id,
                'following_id' => $target->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $notification = Notification::create([
                'user_id' => $target->id,
                'type' => 'follow',
                'data' => [
                    'user_id' => $authUser->id,
                    'username' => $authUser->username,
                ],
            ]);

            broadcast(new NotificationSent($notification)); // ✅ TEMPS RÉEL
        }

        return response()->json([
            'message' => 'Utilisateur suivi.',
        ]);
    }

    public function unfollowUser(Request $request, int $id): JsonResponse
    {
        $authUser = $request->user();

        DB::table('follows')
            ->where('follower_id', $authUser->id)
            ->where('following_id', $id)
            ->delete();

        return response()->json([
            'message' => 'Utilisateur unfollow.',
        ]);
    }

    // ---------------- Friends ----------------

    public function sendFriendRequest(Request $request, int $id): JsonResponse
    {
        $authUser = $request->user();

        // ❌ soi-même
        if ((int) $authUser->id === (int) $id) {
            return response()->json([
                'message' => 'Tu ne peux pas t’ajouter toi-même.',
            ], 422);
        }

        $target = User::findOrFail($id);

        // 🔍 Vérifier relation existante
        $existing = DB::table('friends')
            ->where(function ($query) use ($authUser, $target) {
                $query->where('sender_id', $authUser->id)
                    ->where('receiver_id', $target->id);
            })
            ->orWhere(function ($query) use ($authUser, $target) {
                $query->where('sender_id', $target->id)
                    ->where('receiver_id', $authUser->id);
            })
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Une relation existe déjà.',
            ], 422);
        }

        // ✅ Création demande
        DB::table('friends')->insert([
            'sender_id' => $authUser->id,
            'receiver_id' => $target->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 🔔 Notification
        $notification = Notification::create([
            'user_id' => $target->id,
            'type' => 'friend_request',
            'data' => [
                'user_id' => $authUser->id,
                'username' => $authUser->username,
            ],
        ]);

        // ⚡ Temps réel
        broadcast(new NotificationSent($notification));

        return response()->json([
            'message' => 'Demande d’ami envoyée.',
        ]);
    }

    public function cancelFriendRequest(Request $request, int $id): JsonResponse
    {
        $authUser = $request->user();

        DB::table('friends')
            ->where('sender_id', $authUser->id)
            ->where('receiver_id', $id)
            ->where('status', 'pending')
            ->delete();

        return response()->json([
            'message' => 'Demande d’ami annulée.',
        ]);
    }

    public function acceptFriendRequest(Request $request, int $id): JsonResponse
    {
        $authUser = $request->user();

        DB::table('friends')
            ->where('sender_id', $id)
            ->where('receiver_id', $authUser->id)
            ->update([
                'status' => 'accepted',
                'updated_at' => now(),
            ]);

        $sender = User::findOrFail($id);

        $notification = Notification::create([
            'user_id' => $sender->id,
            'type' => 'friend_accepted',
            'data' => [
                'user_id' => $authUser->id,
                'username' => $authUser->username,
            ],
        ]);

        broadcast(new NotificationSent($notification)); // ✅ TEMPS RÉEL

        return response()->json([
            'message' => 'Demande d’ami acceptée.',
        ]);
    }

    public function declineFriendRequest(Request $request, int $id): JsonResponse
    {
        $authUser = $request->user();

        DB::table('friends')
            ->where('sender_id', $id)
            ->where('receiver_id', $authUser->id)
            ->where('status', 'pending')
            ->delete();

        return response()->json([
            'message' => 'Demande refusée.',
        ]);
    }

    public function removeFriendship(Request $request, int $id): JsonResponse
    {
        $authUser = $request->user();

        DB::table('friends')
            ->where(function ($query) use ($authUser, $id) {
                $query->where('sender_id', $authUser->id)
                    ->where('receiver_id', $id);
            })
            ->orWhere(function ($query) use ($authUser, $id) {
                $query->where('sender_id', $id)
                    ->where('receiver_id', $authUser->id);
            })
            ->where('status', 'accepted')
            ->delete();

        return response()->json([
            'message' => 'Ami supprimé.',
        ]);
    }
}