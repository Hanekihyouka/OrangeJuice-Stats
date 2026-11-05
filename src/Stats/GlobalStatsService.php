<?php

declare(strict_types=1);

namespace OJStats\Stats;

use OJStats\Game\GameData;
use OJStats\Steam\SteamApi;

/**
 * Global (all players) character pick / win counts.
 *
 * Steam's history for a UTC day keeps changing until the day is over and has settled,
 * so the two most recent days (yesterday and today) are always requested live.
 * Every older day is static: it is fetched once and then served from the SQLite cache.
 */
final class GlobalStatsService
{
    /** Number of most recent days (including today) that are never cached. */
    public const LIVE_DAYS = 2;

    public function __construct(
        private readonly SteamApi $steam,
        private readonly GlobalStatsCache $cache,
        private readonly GameData $game,
        private readonly int $earliestDay,
    ) {
    }

    /** All-time totals, always live. */
    public function allTime(): GlobalStats
    {
        $totals = $this->steam->globalTotals($this->statNames());

        return new GlobalStats($this->records($totals), null, 0, 0);
    }

    /**
     * Totals for a range of days. The range is clamped to [earliest day, today].
     */
    public function forRange(DateRange $requested): GlobalStats
    {
        $today = DateRange::today();
        $range = $requested->clamp($this->earliestDay, $today) ?? DateRange::of($today, $today);
        $lastCacheableDay = $today - self::LIVE_DAYS * DateRange::DAY;

        $totals = array_fill_keys($this->statNames(), 0);
        $cachedDays = 0;
        $liveDays = 0;

        if ($cached = $range->clamp($range->first, $lastCacheableDay)) {
            $this->fillCache($cached);
            foreach ($this->cache->sum($cached) as $stat => $sum) {
                if (isset($totals[$stat])) {
                    $totals[$stat] += $sum;
                }
            }
            $cachedDays = $cached->days();
        }

        if ($live = $range->clamp($lastCacheableDay + DateRange::DAY, $range->last)) {
            foreach ($this->steam->globalHistory($this->statNames(), [[$live->first, $live->last]]) as $stat => $days) {
                $totals[$stat] += array_sum($days);
            }
            $liveDays = $live->days();
        }

        return new GlobalStats($this->records($totals), $range, $cachedDays, $liveDays);
    }

    /**
     * Make sure every (stat, day) of $range is in the cache; returns the number of days fetched.
     */
    public function fillCache(DateRange $range): int
    {
        $missing = $this->cache->missing($range, $this->statNames());
        if ($missing['days'] === []) {
            return 0;
        }

        $history = $this->steam->globalHistory($missing['stats'], self::windows($missing['days']));

        // Only store days Steam actually reported; a day absent for every stat
        // (before the game's release, outage, ...) is retried next time.
        $reported = [];
        foreach ($history as $days) {
            $reported += $days;
        }
        $days = array_values(array_filter($missing['days'], static fn (int $day): bool => isset($reported[$day])));
        $this->cache->store($history, $days);

        return count($days);
    }

    /**
     * Group sorted days into contiguous [first, last] windows.
     *
     * @param list<int> $days
     * @return list<array{0: int, 1: int}>
     */
    private static function windows(array $days): array
    {
        $windows = [];
        foreach ($days as $day) {
            $lastIndex = array_key_last($windows);
            if ($lastIndex !== null && $windows[$lastIndex][1] + DateRange::DAY === $day) {
                $windows[$lastIndex][1] = $day;
            } else {
                $windows[] = [$day, $day];
            }
        }

        return $windows;
    }

    /** @return list<string> */
    private function statNames(): array
    {
        $names = [];
        foreach ($this->game->characters() as $character) {
            $names[] = $character->gamesStat;
            $names[] = $character->winsStat;
        }

        return $names;
    }

    /**
     * @param array<string, int> $totals
     * @return list<CharacterRecord>
     */
    private function records(array $totals): array
    {
        $records = [];
        foreach ($this->game->characters() as $character) {
            $records[] = new CharacterRecord(
                $character->name,
                $totals[$character->gamesStat] ?? 0,
                $totals[$character->winsStat] ?? 0,
            );
        }

        return $records;
    }
}
