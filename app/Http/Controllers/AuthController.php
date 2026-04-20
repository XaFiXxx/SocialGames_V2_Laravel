<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'name' => ['required', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'birthday' => ['required', 'date', 'before:today'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'accept_terms' => ['accepted'],
            'newsletter' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'surname' => $validated['surname'],
            'email' => $validated['email'],
            'birthday' => $validated['birthday'],
            'password' => $validated['password'],
            'terms_accepted_at' => now(),
            'terms_version' => 'v1.0',
            'newsletter' => $validated['newsletter'] ?? false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Inscription réussie. Vérifie ton adresse email pour débloquer toutes les fonctionnalités.',
            'user' => $request->user(),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Identifiants invalides',
            ], 422);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $request->user(),
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }
}