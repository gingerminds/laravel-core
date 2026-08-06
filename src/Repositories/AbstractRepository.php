<?php

namespace Gingerminds\LaravelCore\Repositories;

use Gingerminds\LaravelCore\Cache\CacheKeyBuilder;
use Gingerminds\LaravelCore\Models\CacheableResourceInterface;
use Gingerminds\LaravelCore\Models\EagerLoadableModelInterface;
use Gingerminds\LaravelCore\Models\FilterableModelInterface;
use Gingerminds\LaravelCore\Models\SearchableModelInterface;
use Gingerminds\LaravelCore\Models\SortableModelInterface;
use Gingerminds\LaravelCore\Repositories\Filters\FilterHandlerRegistry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @template TModel of Model
 * @implements RepositoryInterface<TModel>
 */
abstract class AbstractRepository implements RepositoryInterface
{
    protected int $perPage = 10;

    abstract public function getModelClass(): string;

    /**
     * @param array<mixed> $with
     * @return LengthAwarePaginator<int,TModel>
     */
    public function get(Request $request, array $with = []): LengthAwarePaginator
    {
        $modelClass = $this->getModelClass();
        $with       = $this->mergeEagerLoads($modelClass, $with);

        $cacheTag = $this->getCacheTag($modelClass);
        $ttl      = $this->resolveCacheTtl($modelClass);

        // Bypass the cache entirely when: the global kill switch is off
        // (config('cache.activate'), env CACHE_ACTIVATE — lets an environment
        // run with caching fully off, e.g. while Redis is unavailable/
        // misconfigured, without touching a single model); the model isn't
        // cacheable at all ($cacheTag === null); or its resolved TTL is
        // zero/negative. Eager loads above still apply in every case, so N+1
        // fixes aren't affected by any of this.
        if (! config('cache.activate', true) || $cacheTag === null || (!is_string($ttl) && $ttl <= 0)) {
            return $this->runGetQuery($request, $with);
        }

        $keyBuilder = app(CacheKeyBuilder::class);
        $context    = $keyBuilder->context();

        if ($this->resolveCacheType($request) === 'item') {
            $id   = $request->input('filters.id');
            $tags = [$cacheTag, $keyBuilder->itemTag($cacheTag, $id)];
            $key  = $keyBuilder->itemKey($cacheTag, $context, $id);
        } else {
            $tags = [$cacheTag, $keyBuilder->listTag($cacheTag)];
            $key  = $keyBuilder->listKey($cacheTag, $context, $request, $with);
        }

        $callback = function () use ($request, $with) {
            return $this->runGetQuery($request, $with);
        };

        // Cache::remember()/put() treat a string $ttl by casting it to an int
        // of seconds — passing the literal 'forever' through would cast to 0
        // and immediately forget() the entry, so 'forever' needs its own
        // dedicated call instead of being funneled through $ttlValue.
        if (is_string($ttl)) {
            return Cache::tags($tags)->rememberForever($key, $callback);
        }

        return Cache::tags($tags)->remember($key, now()->addSeconds($ttl), $callback);
    }

    /**
     * Folds EagerLoadableModelInterface::getEagerLoads() into $with, so N+1
     * fixes apply to every caller automatically instead of relying on each
     * controller/provider to pass eager loads explicitly (in practice, none
     * of them do today).
     *
     * @param array<mixed> $with
     * @return array<mixed>
     */
    protected function mergeEagerLoads(string $modelClass, array $with): array
    {
        if (!is_subclass_of($modelClass, EagerLoadableModelInterface::class)) {
            return $with;
        }

        /** @var class-string<EagerLoadableModelInterface> $modelClass */
        return array_values(array_unique([...$with, ...$modelClass::getEagerLoads()]));
    }

    /**
     * @param array<mixed> $with
     * @return LengthAwarePaginator<int,TModel>
     */
    protected function runGetQuery(Request $request, array $with = []): LengthAwarePaginator
    {
        $page  = $request->query('page', 1);
        $query = $this->getModelClass()::query();
        $this->initGetQueryBuilder($query, $request);
        $this->applyAllFilters($query, $request);

        $query->with($with);

        return $query
            ->paginate((int)$request->query('itemsPerPage', $this->perPage), ['*'], 'page', $page)
            ->withQueryString();
    }

    protected function getCacheTag(string $modelClass): ?string
    {
        if (!is_subclass_of($modelClass, CacheableResourceInterface::class)) {
            return null;
        }

        /** @var class-string<CacheableResourceInterface> $modelClass */
        return $modelClass::getCacheKey();
    }

    protected function resolveCacheTtl(string $modelClass): string|int
    {
        if (!is_subclass_of($modelClass, CacheableResourceInterface::class)) {
            return (int)config('cache.resource_ttl_seconds', 3600);
        }

        /** @var class-string<CacheableResourceInterface> $modelClass */
        $ttl = $modelClass::getCacheTtl();
        if ($ttl !== null) {
            return $ttl;
        }

        return (int)config('cache.resource_ttl_seconds', 3600);
    }

    protected function resolveCacheType(Request $request): string
    {
        return $request->input('filters.id') !== null ? 'item' : 'list';
    }

    /**
     * @param Builder<TModel> $query
     * @return Builder<TModel>
     */
    protected function initGetQueryBuilder(Builder $query, Request $request): Builder
    {
        if (
            $query->getModel() instanceof SortableModelInterface
            && ($request->filled('sort') && $request->filled('sortBy'))
        ) {
            $sortBy    = $request->sortBy;
            $sortOrder = $request->sort;
            $this->applySort($query, $sortBy, $sortOrder);
        }

        return $query;
    }

    /**
     * Add sort to query builder even for BelongsTo relations
     *
     * @param Builder<TModel> $query
     */
    protected function applySort(Builder $query, string $sortBy, string $sortOrder = 'desc'): void
    {
        $model = $query->getModel();
        $table = $model->getTable();

        if (str_contains($sortBy, '.')) {
            [$relationName, $column] = explode('.', $sortBy, 2);

            if (!method_exists($model, $relationName)) {
                return;
            }

            $relation     = $model->{$relationName}();
            $relatedTable = null;

            // BELONGS TO
            if ($relation instanceof BelongsTo) {
                $relatedTable = $relation->getRelated()->getTable();
                $foreignKey   = $relation->getForeignKeyName(); // missions.applicant_id
                $ownerKey     = $relation->getOwnerKeyName();     // applicants.id

                $query->leftJoin(
                    $relatedTable,
                    "$table.$foreignKey",
                    '=',
                    "$relatedTable.$ownerKey"
                );
            }

            // HAS ONE
            if ($relation instanceof HasOne) {
                $relatedTable = $relation->getRelated()->getTable();
                $foreignKey   = $relation->getForeignKeyName(); // needs.mission_id
                $localKey     = $relation->getLocalKeyName();     // missions.id

                $query->leftJoin(
                    $relatedTable,
                    "$relatedTable.$foreignKey",
                    '=',
                    "$table.$localKey"
                );
            }

            if ($relatedTable) {
                $query
                    ->orderBy("$relatedTable.$column", $sortOrder)
                    ->select("$table.*");
            }
        }

        $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * @param Builder<TModel> $query
     */
    protected function applyAllFilters(Builder $query, Request $request): void
    {
        if ($request->query->has('filters')) {
            $filters = $request->all()['filters'];

            $this->getItem($query, $filters);
            $this->applySearch($query, $filters);
            $this->applyFilters($query, $filters);
        }
    }

    /**
     * @param Builder<TModel> $query
     * @param array<mixed> $filters
     */
    protected function getItem(Builder $query, array $filters): void
    {
        if (array_key_exists('id', $filters)) {
            $query->where('id', $filters['id']);
        }
    }

    /**
     * @param Builder<TModel> $query
     * @param array<mixed> $filters
     */
    protected function applySearch(Builder $query, array $filters): void
    {
        $model = $query->getModel();
        if ($model instanceof SearchableModelInterface && array_key_exists('search', $filters)) {
            $search = $filters['search'];

            $query->where(function (Builder $query) use ($search, $model) {
                foreach ($model::getSearchableFields() as $field) {
                    if (str_contains($field, '.')) {
                        [$relation, $column] = explode('.', $field, 2);

                        $query->orWhereHas($relation, function (Builder $relationQuery) use ($column, $search) {
                            $relationQuery->where($column, 'like', '%' . $search . '%');
                        });

                        continue;
                    }

                    $query->orWhere($field, 'like', '%' . $search . '%');
                }
            });
        }
    }

    /**
     * Dispatches each active filter to the handler registered for its `type`
     * in FilterHandlerRegistry. Unregistered types are silently ignored, same
     * as the old `default => null` match arm.
     *
     * @param Builder<TModel> $query
     * @param array<mixed> $filters
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        $model = $query->getModel();

        if (!$model instanceof FilterableModelInterface) {
            return;
        }

        $resourceFilterConfig = $model::getFilters();
        $registry             = app(FilterHandlerRegistry::class);

        foreach ($filters as $property => $value) {
            if (!array_key_exists($property, $resourceFilterConfig)) {
                continue;
            }

            $registry->get($resourceFilterConfig[$property]['type'])?->apply($query, $property, $value);
        }
    }
}
