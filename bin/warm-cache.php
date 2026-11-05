<?php

declare(strict_types=1);

/**
 * Fill the SQLite cache with every finalised day of global stats.
 *
 * Usage: php bin/warm-cache.php [first-day [last-day]]   (days as Y-m-d, UTC)
 *
 * Without arguments the whole history is cached, from earliest_date up to the
 * last day that is no longer changing. Already cached days are skipped, so it is
 * cheap to run regularly (e.g. once a day from cron).
 */

use OJStats\Stats\DateRange;
use OJStats\Stats\GlobalStatsService;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

/** @var \OJStats\App $app */
$app = require __DIR__ . '/../src/bootstrap.php';

$lastCacheable = DateRange::today() - GlobalStatsService::LIVE_DAYS * DateRange::DAY;
$first = DateRange::parseDay($argv[1] ?? $app->config->earliestDate);
$last = isset($argv[2]) ? DateRange::parseDay($argv[2]) : $lastCacheable;
if ($first === null || $last === null) {
    fwrite(STDERR, "Usage: php bin/warm-cache.php [first-day [last-day]]   (Y-m-d)\n");
    exit(2);
}

$range = DateRange::of($first, $last)->clamp($first, $lastCacheable);
if ($range === null) {
    echo "Nothing to cache: days after " . DateRange::format($lastCacheable) . " are still changing.\n";
    exit(0);
}

// Work through the range in blocks so progress is visible and partial work is kept.
$block = 50 * 8 * DateRange::DAY;
$fetched = 0;
for ($start = $range->first; $start <= $range->last; $start += $block) {
    $chunk = DateRange::of($start, min($range->last, $start + $block - DateRange::DAY));
    $count = $app->globalStats()->fillCache($chunk);
    $fetched += $count;
    printf("%s ~ %s: %d day(s) fetched\n", $chunk->firstString(), $chunk->lastString(), $count);
}

printf("Done, %d day(s) fetched, %s ~ %s cached.\n", $fetched, $range->firstString(), $range->lastString());
