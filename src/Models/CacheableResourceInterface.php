<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Models;

/**
 * For resources that are actually served through AbstractRepository (they get
 * their own item/listing cache). A model that only ever needs to invalidate
 * someone else's cache — e.g. a translation row, never queried on its own —
 * should implement CacheCascadeInterface instead, not this one: it has no
 * cache key or TTL of its own to declare.
 */
interface CacheableResourceInterface
{
    public static function getCacheKey(): string;

    public static function getCacheTtlSeconds(): ?int;
}
