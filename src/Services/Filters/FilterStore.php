<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Services\Filters;

/**
 * Default `FilterStoreInterface` implementation — a request-scoped holder
 * for one resource's computed collection-endpoint facets (see
 * `AbstractInjectFiltersMiddleware`, which reads it back out into the
 * response). Bound `scoped()` per resource in each project/package's own
 * service provider — a plain "new instance per resolution" binding
 * wouldn't work here, since the API provider (`set()`) and the middleware
 * (`get()`) resolve it independently within the same request and need to
 * end up with the *same* instance.
 *
 * A resource needing its own DI-resolvable type (so the container can tell
 * one resource's store apart from another's) only needs an empty subclass,
 * e.g. `class PageFilterStore extends FilterStore {}` — this class carries
 * the whole implementation.
 */
class FilterStore implements FilterStoreInterface
{
    /** @var array<string, array{type: string, options: list<array<string, mixed>>|array{min: ?string, max: ?string, years: list<int>}}> */
    private array $filters = [];

    /**
     * @param array<string, array{type: string, options: list<array<string, mixed>>|array{min: ?string, max: ?string, years: list<int>}}> $filters
     */
    public function set(array $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * @return array<string, array{type: string, options: list<array<string, mixed>>|array{min: ?string, max: ?string, years: list<int>}}>
     */
    public function get(): array
    {
        return $this->filters;
    }

    public function isEmpty(): bool
    {
        return $this->filters === [];
    }
}
