<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Cache;

/**
 * Resolves the "viewer" axes that can change what a cacheable resource's
 * query returns for the current request (site, language, country, ...),
 * so that AbstractRepository can fold them into its cache keys.
 *
 * laravel-core has no dependency on laravel-multisite (it's the other way
 * around), so it cannot reference SiteContext/LanguageContext directly.
 * Instead it binds a no-op NullCacheContextResolver by default;
 * laravel-multisite rebinds this to a real resolver in its own service
 * provider, and a consuming project can further extend the binding (the
 * same way LaravelCoreServiceProvider already extends
 * 'api_platform_normalizer_list') to add axes of its own, e.g. a
 * project-specific CountryContext.
 *
 * Implementations must resolve to canonical ids, not raw header strings:
 * "fr" and "fr-FR" must collapse to the same language id and therefore the
 * same cache key.
 */
interface CacheContextResolverInterface
{
    /**
     * @return array<string, int|string|null>
     */
    public function resolve(): array;
}
