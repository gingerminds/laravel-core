<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

abstract class AbstractController extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * @param array<string, mixed>|int|string $editParameters
     * @param array<string, mixed> $indexParameters
     */
    protected function redirectAfterStore(
        string $routePrefix,
        array|int|string $editParameters,
        array $indexParameters = []
    ): RedirectResponse {
        return $this->redirectAfterSave('store', 'index', $routePrefix, $editParameters, $indexParameters);
    }

    /**
     * @param array<string, mixed>|int|string $editParameters
     * @param array<string, mixed> $indexParameters
     */
    protected function redirectAfterUpdate(
        string $routePrefix,
        array|int|string $editParameters,
        array $indexParameters = []
    ): RedirectResponse {
        return $this->redirectAfterSave('update', 'edit', $routePrefix, $editParameters, $indexParameters);
    }

    /**
     * @param array<string, mixed>|int|string $editParameters
     * @param array<string, mixed> $indexParameters
     */
    private function redirectAfterSave(
        string $action,
        string $default,
        string $routePrefix,
        array|int|string $editParameters,
        array $indexParameters
    ): RedirectResponse {
        /** @var array{store?: string, update?: string, resources?: array<string, array<string, string>>} $config */
        $config = config('gingerminds-core.redirect_after_save', []);

        $target = $config['resources'][$routePrefix][$action] ?? $config[$action] ?? $default;

        return $target === 'edit'
            ? redirect()->route($routePrefix . '.edit', $editParameters)
            : redirect()->route($routePrefix . '.index', $indexParameters);
    }
}
