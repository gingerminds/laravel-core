# Facets

Faceted search is the shape behind a collection endpoint where each filter option must show how many results it would yield if selected (e.g. "Concerts (12)", "2024 (5)", "2025 (7)") — each facet's count/bounds are computed against all the *other* currently active filters, not the facet's own. `AbstractFacetRepository` and `AbstractFilterComputeService` give you the shared, reusable shell for this; only the model, its table, and any extra facet types are left to your subclass. Both build on [`FilterableModelInterface::getFilters()`](filters.md) — a model must already declare its filters to use them.

## `AbstractFacetRepository`

`Gingerminds\LaravelCore\Repositories\AbstractFacetRepository<TModel of Model&FilterableModelInterface>` — computes facet-scoped queries on the write/query side.

```php
use Gingerminds\LaravelCore\Repositories\AbstractFacetRepository;
use App\Models\Event\Event;

/**
 * @extends AbstractFacetRepository<Event>
 */
class EventFacetRepository extends AbstractFacetRepository
{
    protected function modelClass(): string
    {
        return Event::class;
    }

    protected function table(): string
    {
        return 'events';
    }
}
```

- `buildFacetedQuery(array $excludeKeys = []): Builder<TModel>` — a query with every currently active filter applied (read from `request()->query('filters')`), except the keys listed in `$excludeKeys`. This is what a given facet's own count/bounds query must run against: every filter *other* than its own, so selecting an option doesn't zero out its own count.
- `getPublishedAtFacetStats(): object{min: ?string, max: ?string, years: list<int>}` — ready-made `published_at` date facet, built on `buildFacetedQuery(['published_at'])` + `DateFacetCalculator`.
- `COUNT_TOTAL_RAW` (`protected const string`, `'COUNT(*) as total'`) — for a subclass's own `selectRaw()` facet-count queries (e.g. grouped by category or select-model option).
- Filter-type dispatch (protected, override/extend for a project-specific filter type): `applyActiveFilters()` iterates active filters and calls `applyFilterByType()`, which routes `select`/`select-model` to `applyFacetedSelectFilter()` (a `whereHas($key, ...)->whereIn('id', ...)`/`where('id', ...)` constraint) and `date` to `applyFacetedDateFilter()` (delegates to `FacetedDateFilter::apply()`). Any other type is a no-op — same silent-ignore behavior as the main [filter handler dispatch](filters.md#custom-filter-types).
- `extraQuerySetup(Builder $query): void` — empty hook, override to add anything the base query always needs (e.g. a scope, an eager load) before filters are applied.

Category-shaped or other domain-specific facet counts (pivot/join tables, visibility rules, ...) are genuinely per-resource and deliberately *not* part of this class — add them as extra public methods on your subclass, following the same `buildFacetedQuery()`-based pattern.

## `DateFacetCalculator`

`Gingerminds\LaravelCore\Repositories\Filters\DateFacetCalculator::compute(Builder $query, string $column): object{min: ?string, max: ?string, years: list<int>}`

Generic min/max/years computation for a date-shaped facet, given a facet base query (all other active filters applied) and a fully-qualified column (e.g. `events.begin_at`). Years are extracted in PHP via Carbon rather than a DB-specific `YEAR()`/`strftime()` call, so behavior is identical under MySQL and SQLite.

## `FacetedDateFilter`

`Gingerminds\LaravelCore\Repositories\Filters\FacetedDateFilter::apply(Builder $query, string $column, mixed $value): void`

Applies a `{from, to}` date-range where-clause to `$column` from a `date`-typed filter's raw value (`'Y-m-d'` strings). Used internally by `AbstractFacetRepository::applyFacetedDateFilter()`; a non-array `$value`, or one with neither `from` nor `to`, is a no-op.

## `AbstractFilterComputeService`

`Gingerminds\LaravelCore\Services\AbstractFilterComputeService<TModel of Model&FilterableModelInterface>` — turns raw facet stats/counts (typically produced by an `AbstractFacetRepository` subclass) into the filter-options shape consumed by the front end, on the read side.

```php
use Gingerminds\LaravelCore\Services\AbstractFilterComputeService;
use App\Models\Event\Event;
use App\Repositories\Event\EventFacetRepository;

/**
 * @extends AbstractFilterComputeService<Event>
 */
class EventFilterComputeService extends AbstractFilterComputeService
{
    public function __construct(private readonly EventFacetRepository $facetRepository)
    {
    }

    protected function modelClass(): string
    {
        return Event::class;
    }

    protected function dateFacetStats(): array
    {
        return [
            'published_at' => $this->facetRepository->getPublishedAtFacetStats(),
        ];
    }
}
```

- `dateFacetStats(): array<string, object{min, max, years}>` (abstract) — one stat object per date facet key, as returned by `DateFacetCalculator::compute()`/`AbstractFacetRepository::getPublishedAtFacetStats()`.
- `computeDateFilters(): array<string, array{type: string, options: array{min, max, years}}>` — maps `dateFacetStats()` into the `{type, options: {min, max, years}}` shape, skipping any facet whose `min` is `null` (nothing to filter on). `type` falls back to `'date'` if the model's `getFilters()` doesn't declare one for that key.
- `activeFilterValue(string $key): mixed` — the currently active raw filter value for `$key` (from `request()->query('filters')`), for a subclass computing e.g. selected ids from it.
- `mergeWithSelected(array $ids, array $selectedIds): array` — merges a facet's counted ids with any currently-selected ones, so an already-selected-but-now-zero-count option still shows instead of disappearing from the list.
- `normalizeIds(mixed $raw): list<int>` — normalizes a raw filter value (`null`, scalar, or array) to a `list<int>`.
- `resolveCategoryOptions(string $categoryModelClass, Collection<int|string, int> $counts, array $selectedIds, bool $orderBySortOrder = false): list<array{value, label, total}>` — shared "categories"-shaped facet resolution: merges counted category ids (`$counts` keyed by category id, valued by total) with `$selectedIds` via `mergeWithSelected()`, drops zero-count/unselected entries, and labels each option by the category's `name` (falling back to `code`). Plain `protected` method, not part of the required contract — a resource without a category taxonomy has no obligation to call it.

## See also

- [Filters](filters.md) — declaring `getFilters()` on the model, which both this and the filters panel consume.
- [Resource Model](../ResourceModel.md#optional-interfaces) — `FilterableModelInterface`.
- [API](../API.md) — exposing facet stats/options through an API provider's collection response.
