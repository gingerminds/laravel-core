<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Dates are stored in UTC; back-office inputs, displays and date filters use `gingerminds-core.display_timezone`.
 */
final class DisplayTimezone
{
    public static function name(): string
    {
        return (string) (config('gingerminds-core.display_timezone') ?? config('app.timezone'));
    }

    public static function toUtc(?string $value): ?Carbon
    {
        return filled($value) ? Carbon::parse($value, self::name())->utc() : null;
    }

    public static function toDisplay(CarbonInterface|string|null $date): ?Carbon
    {
        return $date === null ? null : Carbon::parse($date)->setTimezone(self::name());
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon} UTC bounds of the `Y-m-d` days, read in the display timezone
     */
    public static function dayRangeToUtc(?string $from, ?string $to): array
    {
        return [
            filled($from) ? Carbon::createFromFormat('Y-m-d', $from, self::name())?->startOfDay()->utc() : null,
            filled($to) ? Carbon::createFromFormat('Y-m-d', $to, self::name())?->endOfDay()->utc() : null,
        ];
    }
}
