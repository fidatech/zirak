<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/** Per-day numbers for small charts: one grouped query, missing days filled with 0. */
class DailySeries
{
    /** @return array<string, int|float> date => value, oldest first */
    public static function make(Builder $query, string $dateColumn, int $days, string $aggregate = 'count', string $column = '*'): array
    {
        $from = CarbonImmutable::today()->subDays($days - 1);
        $rows = (clone $query)->where($dateColumn, '>=', $from)
            ->selectRaw("DATE({$dateColumn}) as d, {$aggregate}({$column}) as v")
            ->groupBy('d')->pluck('v', 'd');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $from->addDays($i)->toDateString();
            $series[$day] = (float) ($rows[$day] ?? 0);
        }

        return $series;
    }

    /** Change between the last $days and the $days before, in percent (null when there is nothing to compare). */
    public static function change(Builder $query, string $dateColumn, int $days = 7, string $aggregate = 'count', string $column = '*'): ?int
    {
        $now = CarbonImmutable::now();
        $current = (float) (clone $query)->where($dateColumn, '>=', $now->subDays($days))->{$aggregate}($column === '*' ? 'id' : $column);
        $previous = (float) (clone $query)->whereBetween($dateColumn, [$now->subDays(2 * $days), $now->subDays($days)])->{$aggregate}($column === '*' ? 'id' : $column);

        return $previous > 0 ? (int) round(100 * ($current - $previous) / $previous) : null;
    }
}
