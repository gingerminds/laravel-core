<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Services\Filters;

/**
 * A per-request store holding a resource's computed `filters` (see
 * `AbstractFilterComputeService`), written by that resource's own
 * ApiProvider and read back by its `AbstractInjectFiltersMiddleware`
 * subclass to merge into the response body.
 */
interface FilterStoreInterface
{
    /**
     * @param array<string, array{type: string, options: list<array<string, mixed>>|array{min: ?string, max: ?string, years: list<int>}}> $filters
     */
    public function set(array $filters): void;

    /**
     * @return array<string, array{type: string, options: list<array<string, mixed>>|array{min: ?string, max: ?string, years: list<int>}}>
     */
    public function get(): array;

    public function isEmpty(): bool;
}
