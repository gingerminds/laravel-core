<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Http\Middelware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAreaIsAuthenticated
{
    /**
     * @var string[]
     */
    private const array GUEST_ROUTE_NAMES = [
        'gingerminds-core.login',
        'gingerminds-core.authenticate',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $prefix    = trim((string) config('gingerminds-core.admin_prefix'), '/');
        $routeName = $request->route()?->getName();

        $isAdminRoute = $prefix    !== ''      && $request->is($prefix, $prefix . '/*');
        $isGuestRoute = $routeName !== null && in_array($routeName, self::GUEST_ROUTE_NAMES, true);

        if (! $isAdminRoute || $isGuestRoute || Auth::guard('web')->check()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(401);
        }

        return redirect()->guest(route('gingerminds-core.login'));
    }
}
