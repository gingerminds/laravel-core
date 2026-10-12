<?php

namespace Gingerminds\LaravelCore\Repositories\Filters\Handlers;

use Gingerminds\LaravelCore\Repositories\Filters\FilterHandlerInterface;
use Gingerminds\LaravelCore\Support\DisplayTimezone;
use Illuminate\Database\Eloquent\Builder;

class DateFilterHandler implements FilterHandlerInterface
{
    public function apply(Builder $query, string $property, mixed $value): void
    {
        if (!is_array($value)) {
            return;
        }

        [$from, $to] = DisplayTimezone::dayRangeToUtc($value['from'] ?? null, $value['to'] ?? null);

        $table         = $query->getModel()->getTable();
        $tableProperty = str_contains($property, '.') ? $property : "$table.$property";

        if ($from && $to) {
            $query->whereBetween($tableProperty, [$from, $to]);
        } elseif ($from) {
            $query->where($tableProperty, '>=', $from);
        } elseif ($to) {
            $query->where($tableProperty, '<=', $to);
        }
    }
}
