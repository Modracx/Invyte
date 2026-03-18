<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->input('email'))
            ->where('is_active', 1)
            ->first();

        if (!$user || !password_verify($request->input('password'), $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $this->generateToken($user);

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'assigned_sources' => $user->isAdmin() ? null : $user->getAssignedSourceCodes(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        // Stateless JWT — client discards token
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user');
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'assigned_sources' => $user->isAdmin() ? null : $user->getAssignedSourceCodes(),
        ]);
    }

    private function generateToken(User $user): string
    {
        $now = time();
        $ttl = (int) env('JWT_TTL', 480); // minutes
        $payload = [
            'iss' => env('APP_URL'),
            'sub' => $user->id,
            'iat' => $now,
            'exp' => $now + ($ttl * 60),
            'role' => $user->role,
        ];
        return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    }
}
