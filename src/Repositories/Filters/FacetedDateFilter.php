<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Repositories\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class FacetedDateFilter
{
    /**
     * @param  Builder<*>  $query
     * @param  array{from?: string, to?: string}|mixed  $value
     */
    public static function apply(Builder $query, string $column, mixed $value): void
    {
        if (! is_array($value)) {
            return;
        }

        $from = empty($value['from']) ? null : Carbon::createFromFormat('Y-m-d', $value['from'])?->startOfDay();
        $to   = empty($value['to']) ? null : Carbon::createFromFormat('Y-m-d', $value['to'])?->endOfDay();

        if (! $from && ! $to) {
            return;
        }

        if ($from && $to) {
            $query->whereBetween($column, [$from, $to]);
        } elseif ($from) {
            $query->where($column, '>=', $from);
        } else {
            $query->where($column, '<=', $to);
        }
    }
}
