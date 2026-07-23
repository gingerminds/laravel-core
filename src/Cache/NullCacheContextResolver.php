<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Cache;

/**
 * Default CacheContextResolverInterface binding when no package/project adds
 * a real one: no viewer axis affects the cache key beyond the request's own
 * filters, which AbstractRepository already accounts for.
 */
class NullCacheContextResolver implements CacheContextResolverInterface
{
    /**
     * @return array<string, int|string|null>
     */
    public function resolve(): array
    {
        return [];
    }
}
