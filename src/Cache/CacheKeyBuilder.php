<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Cache;

use Illuminate\Http\Request;

/**
 * Single source of truth for cacheable-resource key/tag naming, shared by
 * AbstractRepository (read path) and LaravelCoreServiceProvider's write-path
 * flush listener — both must agree on the exact same format, otherwise a
 * write could invalidate/repopulate a key the read path never looks at.
 */
class CacheKeyBuilder
{
    public function __construct(
        private readonly CacheContextResolverInterface $contextResolver
    ) {
    }

    /**
     * @return array<string, int|string|null>
     */
    public function context(): array
    {
        return $this->contextResolver->resolve();
    }

    /**
     * Tag scoping every cached representation of a single row of this
     * resource, across every viewer context. Flushing just this tag clears
     * that one item without touching any other item or any listing.
     */
    public function itemTag(string $resourceTag, int|string $id): string
    {
        return "{$resourceTag}:item:{$id}";
    }

    /**
     * Tag scoping every listing/pagination entry of this resource, across
     * every viewer context and filter combination.
     */
    public function listTag(string $resourceTag): string
    {
        return "{$resourceTag}:list";
    }

    /**
     * Deterministic per-id key: doesn't just get invalidated, it can be
     * written to directly on save, so a write can repopulate the item cache
     * instead of only clearing it.
     *
     * @param array<string, int|string|null> $context
     */
    public function itemKey(string $resourceTag, array $context, int|string $id): string
    {
        return "{$resourceTag}:item:" . md5(serialize($context)) . ":{$id}";
    }

    /**
     * Listing keys stay hash-based: there is no bounded set of
     * filter/sort/pagination combinations to address deterministically, so
     * these can only ever be invalidated wholesale and recomputed lazily on
     * the next read.
     *
     * @param array<string, int|string|null> $context
     * @param array<mixed> $with
     */
    public function listKey(string $resourceTag, array $context, Request $request, array $with): string
    {
        return "{$resourceTag}:list:" . md5(serialize($context))
            . ':' . md5(serialize($request->all()) . serialize($with));
    }
}
