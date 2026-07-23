<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Models\Trait;

/**
 * Default implementation of CacheableResourceInterface::getCacheTtlSeconds():
 * null, i.e. fall back to config('cache.resource_ttl_seconds'). Models that
 * implement the interface should `use` this trait and only declare
 * getCacheKey() — override getCacheTtlSeconds() only if this particular
 * resource needs a TTL other than the project-wide default.
 */
trait CacheableResourceTrait
{
    public static function getCacheTtlSeconds(): ?int
    {
        return null;
    }
}
