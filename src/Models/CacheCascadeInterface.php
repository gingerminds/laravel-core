<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Models;

/**
 * Independent from CacheableResourceInterface on purpose: plenty of models
 * (translation rows, child rows) are never themselves served/cached through
 * AbstractRepository, but still need to invalidate a *parent* resource's
 * cache when they change (e.g. a translation embedded in its parent's
 * cached representation). Such a model implements only this interface —
 * one method — instead of being forced to also declare a cache key and TTL
 * it will never use.
 *
 * A model can of course implement both this and CacheableResourceInterface
 * if it's independently cacheable *and* needs to cascade to another
 * resource, but that's the exception, not the default case.
 */
interface CacheCascadeInterface
{
    /**
     * Cache keys (as returned by other resources' getCacheKey()) that must
     * be fully invalidated whenever this model is saved/deleted/restored.
     *
     * @return array<int, string>
     */
    public static function getCascadeCacheKeys(): array;
}
