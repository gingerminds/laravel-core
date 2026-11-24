<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Repositories\Filters;

use Gingerminds\LaravelCore\Support\DisplayTimezone;
use Illuminate\Database\Eloquent\Builder;

class FacetedDateFilter
{
    /**
     * `include_empty` also matches rows whose date is NULL.
     *
     * @param  Builder<*>  $query
     * @param  array{from?: string, to?: string, include_empty?: bool|string}|mixed  $value
     */
    public static function apply(Builder $query, string $column, mixed $value): void
    {
        if (! is_array($value)) {
            return;
        }

        [$from, $to] = DisplayTimezone::dayRangeToUtc($value['from'] ?? null, $value['to'] ?? null);

        if (! $from && ! $to) {
            return;
        }

        $range = function (Builder $q) use ($column, $from, $to): void {
            if ($from && $to) {
                $q->whereBetween($column, [$from, $to]);
            } elseif ($from) {
                $q->where($column, '>=', $from);
            } else {
                $q->where($column, '<=', $to);
            }
        };

        if (! self::includesEmpty($value)) {
            $range($query);

            return;
        }

        $query->where(fn (Builder $q) => $q->where($range)->orWhereNull($column));
    }

    /**
     * @param  array<mixed>  $value
     */
    public static function includesEmpty(array $value): bool
    {
        return filter_var($value['include_empty'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }
}
