<?php

namespace Gingerminds\LaravelCore\Repositories\Filters\Handlers;

use Gingerminds\LaravelCore\Repositories\Filters\FacetedDateFilter;
use Gingerminds\LaravelCore\Repositories\Filters\FilterHandlerInterface;
use Illuminate\Database\Eloquent\Builder;

class DateFilterHandler implements FilterHandlerInterface
{
    public function apply(Builder $query, string $property, mixed $value): void
    {
        $table = $query->getModel()->getTable();

        FacetedDateFilter::apply($query, str_contains($property, '.') ? $property : "$table.$property", $value);
    }
}
