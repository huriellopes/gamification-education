<?php

declare(strict_types=1);

namespace App\Services\Concerns;

trait BuildsDailyChart
{
    /**
     * Build a gap-filled daily series for the last N days.
     *
     * Takes a map of "Y-m-d" => value (with possible missing days) and returns
     * an ordered list with one entry per day, labelled "d/m", filling gaps with 0.
     *
     * @param  array<string, int>  $raw  values keyed by "Y-m-d"
     * @return list<array<string, int|string>>
     */
    protected function dailyChart(array $raw, string $valueKey, int $days = 7): array
    {
        $series = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $series[] = [
                'day' => $date->format('d/m'),
                $valueKey => $raw[$date->format('Y-m-d')] ?? 0,
            ];
        }

        return $series;
    }
}
