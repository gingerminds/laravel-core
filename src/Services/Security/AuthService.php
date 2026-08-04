<?php

namespace Gingerminds\LaravelCore\Services\Security;

use Exception;
use Gingerminds\LaravelCore\Http\Requests\Security\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * @return array<mixed>
     */
    public function login(LoginRequest $request): array
    {
        try {
            $credentials = $request->validate([
                'username' => 'required|email',
                'password' => 'required',
            ]);

            $throttleKey = Str::lower($credentials['username']) . '|' . $request->ip();

            if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
                throw new Exception('USER_BLOCKED', 403);
            }

            if (
                !Auth::attempt([
                'email'    => $credentials['username'],
                'password' => $credentials['password'],
                ], $request->boolean('remember'))
            ) {
                RateLimiter::hit($throttleKey);
                throw new Exception('CREDENTIALS_NOT_VALID', 401);
            }

            if (!$this->isDomainAuthorized($request)) {
                throw new Exception('USER_NOT_AUTHORIZED', 401);
            }

            RateLimiter::clear($throttleKey);

            return [
                'success' => true,
                'data'    => ['user' => Auth::user()],
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'message' => $this->getFriendlyMessage($e->getMessage()),
                'status'  => (int) $e->getCode(),
            ];
        }
    }

    /**
     * Extrait la logique de domaine pour réduire la complexité
     */
    protected function isDomainAuthorized(LoginRequest $request): bool
    {
        if (config('app.env') === 'local') {
            return true;
        }

        $origin = $request->header('origin');

        if (!$origin) {
            return false;
        }

        $host = parse_url($origin, PHP_URL_HOST);

        $authorizedDomains = config('auth.authorized_domains', []);

        return in_array($host, $authorizedDomains, true);
    }

    /**
     * Centralise les messages d'erreur
     */
    protected function getFriendlyMessage(string $errorCode): string
    {
        return match ($errorCode) {
            'USER_BLOCKED'          => 'Trop de tentatives. Réessayez dans 5 minutes.',
            'USER_NOT_AUTHORIZED'   => 'Domaine non autorisé.',
            'CREDENTIALS_NOT_VALID' => 'Identifiants incorrects.',
            default                 => 'Une erreur est survenue.',
        };
    }
}
