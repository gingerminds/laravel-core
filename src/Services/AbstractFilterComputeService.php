<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Services;

use Gingerminds\LaravelCore\Models\FilterableModelInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @template TModel of Model&FilterableModelInterface
 */
abstract class AbstractFilterComputeService
{
    /**
     * @return class-string<TModel>
     */
    abstract protected function modelClass(): string;

    /**
     * @return array<string, object{min: ?string, max: ?string, years: list<int>}>
     */
    abstract protected function dateFacetStats(): array;

    /**
     * @return array<string, array{type: string, options: array{min: ?string, max: ?string, years: list<int>}}>
     */
    protected function computeDateFilters(): array
    {
        $filterConfig = ($this->modelClass())::getFilters();

        $result = [];

        foreach ($this->dateFacetStats() as $key => $stat) {
            if ($stat->min === null) {
                continue;
            }

            $result[$key] = [
                'type'    => $filterConfig[$key]['type'] ?? 'date',
                'options' => [
                    'min'   => $stat->min,
                    'max'   => $stat->max,
                    'years' => $stat->years,
                ],
            ];
        }

        return $result;
    }

    protected function activeFilterValue(string $key): mixed
    {
        $activeFilters = (array) request()->query('filters', []);

        return $activeFilters[$key] ?? null;
    }

    /**
     * @param  list<int|string>  $ids
     * @param  list<int>  $selectedIds
     * @return list<int|string>
     */
    protected function mergeWithSelected(array $ids, array $selectedIds): array
    {
        foreach ($selectedIds as $selectedId) {
            if (! in_array($selectedId, $ids, true)) {
                $ids[] = $selectedId;
            }
        }

        return $ids;
    }

    /**
     * @return list<int>
     */
    protected function normalizeIds(mixed $raw): array
    {
        return match (true) {
            is_array($raw) => array_values(array_map('intval', $raw)),
            $raw !== null  => [(int) $raw],
            default        => [],
        };
    }

    /**
     * @param  class-string<Model>  $categoryModelClass
     * @param  Collection<int|string, int>  $counts  category id => total, see facet repository's *_category_id/total row shape
     * @param  list<int>  $selectedIds
     * @return list<array{value: int|string, label: string, total: int}>
     */
    protected function resolveCategoryOptions(
        string $categoryModelClass,
        Collection $counts,
        array $selectedIds,
        bool $orderBySortOrder = false,
    ): array {
        $categoryIds = $this->mergeWithSelected(array_values($counts->keys()->all()), $selectedIds);

        if ($categoryIds === []) {
            return [];
        }

        $query = $categoryModelClass::query()->whereIn('id', $categoryIds);

        if ($orderBySortOrder) {
            $query->orderBy('sort_order');
        }

        /** @var Collection<int, Model> $categories */
        $categories = $query->get()->keyBy('id');

        $result = [];

        foreach ($categories as $id => $category) {
            $total      = $counts[$id] ?? 0;
            $isSelected = in_array((int) $id, $selectedIds, true);

            if ($total === 0 && ! $isSelected) {
                continue;
            }

            $result[] = [
                'value' => $id,
                'label' => $category->getAttribute('name') ?? $category->getAttribute('code'),
                'total' => $total,
            ];
        }

        return $result;
    }
}
