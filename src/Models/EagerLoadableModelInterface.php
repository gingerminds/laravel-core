<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Models;

/**
 * Declares which relations must always be preloaded when this model is
 * queried through AbstractRepository, regardless of whether it is also
 * cacheable. This exists to fix N+1 query patterns at the source instead of
 * relying on every controller/provider to remember to pass a $with array
 * (in practice, none of them ever did).
 */
interface EagerLoadableModelInterface
{
    /**
     * @return array<int, string>
     */
    public static function getEagerLoads(): array;
}
