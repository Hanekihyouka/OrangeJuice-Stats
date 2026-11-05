<?php

declare(strict_types=1);

namespace OJStats\Stats;

/**
 * SQLite store of finalised global daily stats (one row per stat per UTC day).
 * Only days that can no longer change are written here, see GlobalStatsService.
 */
final class GlobalStatsCache
{
    private ?\PDO $pdo = null;

    public function __construct(private readonly string $path)
    {
    }

    /**
     * Days within $range for which at least one of $stats is not stored yet,
     * and the stats that are incomplete within $range.
     *
     * @param list<string> $stats
     * @return array{days: list<int>, stats: list<string>}
     */
    public function missing(DateRange $range, array $stats): array
    {
        $params = [$range->firstString(), $range->lastString(), ...$stats];
        $in = implode(',', array_fill(0, count($stats), '?'));

        $perDay = $this->query("SELECT day, COUNT(*) FROM global_daily_stats WHERE day BETWEEN ? AND ? AND stat IN ($in) GROUP BY day", $params)
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
        $days = array_values(array_filter(
            $range->eachDay(),
            static fn (int $day): bool => (int) ($perDay[DateRange::format($day)] ?? 0) < count($stats),
        ));

        $perStat = $this->query("SELECT stat, COUNT(*) FROM global_daily_stats WHERE day BETWEEN ? AND ? AND stat IN ($in) GROUP BY stat", $params)
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
        $incomplete = array_values(array_filter(
            $stats,
            static fn (string $stat): bool => (int) ($perStat[$stat] ?? 0) < $range->days(),
        ));

        return ['days' => $days, 'stats' => $incomplete];
    }

    /**
     * @param array<string, array<int, int>> $history stat => [day timestamp => count]
     * @param list<int> $days days to store; a stat without an entry for one of them is stored as 0
     */
    public function store(array $history, array $days): void
    {
        $pdo = $this->pdo();
        $insert = $pdo->prepare('INSERT OR REPLACE INTO global_daily_stats (day, stat, total) VALUES (?, ?, ?)');

        $pdo->beginTransaction();
        try {
            foreach ($days as $day) {
                $date = DateRange::format($day);
                foreach ($history as $stat => $counts) {
                    $insert->execute([$date, $stat, $counts[$day] ?? 0]);
                }
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @return array<string, int> stat => sum over the range
     */
    public function sum(DateRange $range): array
    {
        $rows = $this->query('SELECT stat, SUM(total) FROM global_daily_stats WHERE day BETWEEN ? AND ? GROUP BY stat', [
            $range->firstString(),
            $range->lastString(),
        ])->fetchAll(\PDO::FETCH_KEY_PAIR);

        return array_map('intval', $rows);
    }

    /** @param list<string|int> $params */
    private function query(string $sql, array $params): \PDOStatement
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    private function pdo(): \PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $dir = dirname($this->path);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException("Cannot create database directory $dir");
        }

        $pdo = new \PDO('sqlite:' . $this->path, options: [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA busy_timeout = 10000');
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS global_daily_stats (
                day   TEXT    NOT NULL, -- UTC day, Y-m-d
                stat  TEXT    NOT NULL, -- Steam stat name
                total INTEGER NOT NULL,
                PRIMARY KEY (day, stat)
            ) WITHOUT ROWID
            SQL);

        return $this->pdo = $pdo;
    }
}
