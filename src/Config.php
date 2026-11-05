<?php

declare(strict_types=1);

namespace OJStats;

/**
 * Application configuration.
 *
 * Values come from (later wins):
 *   1. built-in defaults
 *   2. config.php in the project root (optional, returns an array)
 *   3. environment variables (OJ_*)
 */
final class Config
{
    /** config key => environment variable */
    private const ENV = [
        'steam_api_key' => 'OJ_STEAM_API_KEY',
        'base_url' => 'OJ_BASE_URL',
        'database' => 'OJ_DATABASE',
        'earliest_date' => 'OJ_EARLIEST_DATE',
        'http_timeout' => 'OJ_HTTP_TIMEOUT',
        'player_cache_ttl' => 'OJ_PLAYER_CACHE_TTL',
        'player_cache_dir' => 'OJ_PLAYER_CACHE_DIR',
    ];

    private function __construct(
        /** Steam Web API key, https://steamcommunity.com/dev/apikey */
        public readonly string $steamApiKey,
        /** Public base URL of the site (e.g. https://stats.example.com). Empty = derive from the request. */
        public readonly string $baseUrl,
        /** Path of the SQLite database caching global daily stats. */
        public readonly string $databasePath,
        /** First day (UTC, Y-m-d) for which Steam has global history. */
        public readonly string $earliestDate,
        /** Timeout in seconds for a single Steam API request. */
        public readonly int $httpTimeout,
        /** Seconds a player's cached data is reused before Steam is asked again. 0 = no cache. */
        public readonly int $playerCacheTtl,
        /** Directory of the per-player cache files. */
        public readonly string $playerCacheDir,
    ) {
    }

    public static function load(string $root): self
    {
        $values = [
            'steam_api_key' => '',
            'base_url' => '',
            'database' => $root . '/var/global-stats.sqlite',
            'earliest_date' => '2014-05-31',
            'http_timeout' => 15,
            'player_cache_ttl' => 3600,
            'player_cache_dir' => $root . '/var/players',
        ];

        $file = $root . '/config.php';
        if (is_file($file)) {
            $fileValues = require $file;
            if (!is_array($fileValues)) {
                throw new \RuntimeException('config.php must return an array, see config.example.php');
            }
            $values = array_replace($values, $fileValues);
        }

        foreach (self::ENV as $key => $env) {
            $value = getenv($env);
            if ($value !== false && $value !== '') {
                $values[$key] = $value;
            }
        }

        return new self(
            steamApiKey: trim((string) $values['steam_api_key']),
            baseUrl: rtrim(trim((string) $values['base_url']), '/'),
            databasePath: (string) $values['database'],
            earliestDate: (string) $values['earliest_date'],
            httpTimeout: max(1, (int) $values['http_timeout']),
            playerCacheTtl: max(0, (int) $values['player_cache_ttl']),
            playerCacheDir: rtrim((string) $values['player_cache_dir'], '/'),
        );
    }
}
