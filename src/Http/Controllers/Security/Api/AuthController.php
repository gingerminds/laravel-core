<?php

namespace Gingerminds\LaravelCore\Http\Controllers\Security\Api;

use Gingerminds\LaravelCore\Contracts\Auth\LoginResponseEnricherInterface;
use Gingerminds\LaravelCore\Http\Controllers\AbstractController;
use Gingerminds\LaravelCore\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends AbstractController
{
    public function login(Request $request): JsonResponse
    {
        $throttleKey = Str::lower((string) $request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json(
                ['message' => 'Trop de tentatives. Réessayez dans 5 minutes.'],
                429,
                ['Retry-After' => RateLimiter::availableIn($throttleKey)]
            );
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($throttleKey, 300);

            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        RateLimiter::clear($throttleKey);

        /** @var User $user */
        $user  = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        $data = [
            'token'      => $token,
            'token_type' => 'Bearer',
        ];

        /** @var LoginResponseEnricherInterface $enricher */
        foreach (app()->tagged('gingerminds-core.login-enrichers') as $enricher) {
            $data = $enricher->enrich($user, $data);
        }

        return response()->json($data);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->validate([
            'revoke_all' => ['sometimes', 'boolean'],
        ]);

        /** @var User|null $user */
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $revokeAll = (bool) $request->boolean('revoke_all');
        if ($revokeAll) {
            $user->tokens()->delete();
        } else {
            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out'], 200);
    }
}
