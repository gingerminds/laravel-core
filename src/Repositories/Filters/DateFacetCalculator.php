<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Repositories\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DateFacetCalculator
{
    /**
     * @param  Builder<*>  $query
     * @return object{min: ?string, max: ?string, years: list<int>}
     */
    public static function compute(Builder $query, string $column): object
    {
        $base = $query->toBase()->whereNotNull($column);

        /** @var object{min: ?string, max: ?string}|null $bounds */
        $bounds = (clone $base)
            ->selectRaw("MIN({$column}) as min, MAX({$column}) as max")
            ->first();

        $years = array_values(
            (clone $base)
                ->select($column)
                ->distinct()
                ->pluck($column)
                ->map(static fn (mixed $value): int => Carbon::parse((string) $value)->year)
                ->unique()
                ->sortDesc()
                ->all()
        );

        return (object) [
            'min'   => $bounds?->min !== null ? Carbon::parse($bounds->min)->toDateString() : null,
            'max'   => $bounds?->max !== null ? Carbon::parse($bounds->max)->toDateString() : null,
            'years' => $years,
        ];
    }
}
