<?php

declare(strict_types=1);

namespace OJStats\Stats;

use OJStats\Steam\SteamId;

/**
 * File cache of single players' Steam data: one JSON text file per player,
 * <dir>/<steamid>.json, holding the time of the query and Steam's raw answers.
 */
final class PlayerCache
{
    private const VERSION = 1;

    public function __construct(private readonly string $dir)
    {
    }

    /**
     * Cached entry of the player if it is not older than $ttl seconds.
     *
     * @return array{fetched_at: int, stats: array<string, int>, summary: array<string, mixed>}|null
     */
    public function get(SteamId $steamId, int $ttl, ?int $now = null): ?array
    {
        $json = @file_get_contents($this->file($steamId));
        if ($json === false) {
            return null;
        }
        $entry = json_decode($json, true);
        if (!is_array($entry)
            || ($entry['version'] ?? null) !== self::VERSION
            || !is_int($entry['fetched_at'] ?? null)
            || !is_array($entry['stats'] ?? null)
            || !is_array($entry['summary'] ?? null)
        ) {
            return null; // unreadable or from another format: treat as a miss
        }
        if (($now ?? time()) - $entry['fetched_at'] >= $ttl) {
            return null;
        }

        return ['fetched_at' => $entry['fetched_at'], 'stats' => $entry['stats'], 'summary' => $entry['summary']];
    }

    /**
     * @param array<string, int> $stats
     * @param array<string, mixed> $summary
     */
    public function put(SteamId $steamId, int $fetchedAt, array $stats, array $summary): void
    {
        if (!is_dir($this->dir) && !@mkdir($this->dir, 0775, true) && !is_dir($this->dir)) {
            throw new \RuntimeException("Cannot create player cache directory {$this->dir}");
        }

        $json = json_encode([
            'version' => self::VERSION,
            'steamid' => $steamId->value,
            'fetched_at' => $fetchedAt,
            'fetched_at_utc' => gmdate('Y-m-d H:i:s', $fetchedAt),
            'summary' => $summary,
            'stats' => $stats,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        // Write to a temporary file first so readers never see a half written file.
        $file = $this->file($steamId);
        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (@file_put_contents($tmp, $json . "\n") === false || !@rename($tmp, $file)) {
            @unlink($tmp);
            throw new \RuntimeException("Cannot write player cache file $file");
        }
    }

    private function file(SteamId $steamId): string
    {
        return $this->dir . '/' . $steamId->value . '.json';
    }
}
