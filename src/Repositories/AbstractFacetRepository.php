<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Repositories;

use Gingerminds\LaravelCore\Models\FilterableModelInterface;
use Gingerminds\LaravelCore\Repositories\Filters\DateFacetCalculator;
use Gingerminds\LaravelCore\Repositories\Filters\FacetedDateFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model&FilterableModelInterface
 */
abstract class AbstractFacetRepository
{
    protected const string COUNT_TOTAL_RAW = 'COUNT(*) as total';

    /**
     * @return class-string<TModel>
     */
    abstract protected function modelClass(): string;

    abstract protected function table(): string;

    /**
     * @param  list<string>  $excludeKeys
     * @return Builder<TModel>
     */
    public function buildFacetedQuery(array $excludeKeys = []): Builder
    {
        /** @var Builder<TModel> $query */
        $query = ($this->modelClass())::query();

        $this->extraQuerySetup($query);
        $this->applyActiveFilters($query, $excludeKeys);

        return $query;
    }

    /**
     * @return object{min: ?string, max: ?string, years: list<int>}
     */
    public function getPublishedAtFacetStats(): object
    {
        return DateFacetCalculator::compute(
            $this->buildFacetedQuery(['published_at']),
            $this->table() . '.published_at',
        );
    }

    /**
     * @param  Builder<TModel>  $query
     * @param  list<string>  $excludeKeys
     */
    protected function applyActiveFilters(Builder $query, array $excludeKeys): void
    {
        $filters      = (array) request()->query('filters', []);
        $filterConfig = ($this->modelClass())::getFilters();

        foreach ($filters as $key => $value) {
            if (in_array($key, $excludeKeys, true)) {
                continue;
            }

            if (! array_key_exists($key, $filterConfig)) {
                continue;
            }

            $this->applyFilterByType($query, $filterConfig[$key]['type'], $key, $value);
        }
    }

    /** @param Builder<TModel> $query */
    protected function applyFilterByType(Builder $query, string $type, string $key, mixed $value): void
    {
        match ($type) {
            'select', 'select-model' => $this->applyFacetedSelectFilter($query, $key, $value),
            'date'                   => $this->applyFacetedDateFilter($query, $key, $value),
            default                  => null,
        };
    }

    /** @param Builder<TModel> $query */
    protected function applyFacetedSelectFilter(Builder $query, string $key, mixed $value): void
    {
        if ($value === null || $value === 'all') {
            return;
        }

        $query->whereHas($key, function (Builder $q) use ($value) {
            $table = $q->getModel()->getTable();

            if (is_array($value)) {
                $q->whereIn($table . '.id', $value);
            } else {
                $q->where($table . '.id', $value);
            }
        });
    }

    /** @param Builder<TModel> $query */
    protected function applyFacetedDateFilter(Builder $query, string $key, mixed $value): void
    {
        FacetedDateFilter::apply($query, $this->table() . '.' . $key, $value);
    }

    /** @param Builder<TModel> $query */
    protected function extraQuerySetup(Builder $query): void
    {
    }
}
