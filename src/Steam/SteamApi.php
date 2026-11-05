<?php

declare(strict_types=1);

namespace OJStats\Steam;

use OJStats\Http\HttpClient;
use OJStats\Http\HttpResponse;

/**
 * Thin wrapper around the public Steam Web API endpoints used by this project.
 */
final class SteamApi
{
    public const APP_ID = 282800; // 100% Orange Juice

    private const URL_PLAYER_STATS = 'https://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v2/';
    private const URL_PLAYER_SUMMARIES = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/';
    private const URL_GLOBAL_STATS = 'https://api.steampowered.com/ISteamUserStats/GetGlobalStatsForGame/v1/';

    /** GetGlobalStatsForGame accepts at most 100 stat names per request. */
    public const GLOBAL_MAX_NAMES = 100;
    /** Max number of days requested per GetGlobalStatsForGame call. */
    public const GLOBAL_MAX_DAYS = 50;

    public function __construct(
        private readonly HttpClient $http,
        private readonly string $apiKey,
    ) {
    }

    /**
     * In-game stats of one player.
     *
     * @return array<string, int> stat name => value (stats the player never touched are absent)
     */
    public function playerStats(SteamId $steamId): array
    {
        $response = $this->http->get($this->url(self::URL_PLAYER_STATS, [
            'appid' => self::APP_ID,
            'key' => $this->requireKey(),
            'steamid' => $steamId->value,
        ]));

        // Steam answers private / unknown profiles with "400 {}" or "403".
        if ($response->error === null && in_array($response->status, [400, 403], true)) {
            throw new PrivateStatsException("Stats of $steamId are not public");
        }
        $stats = $this->decode($response)['playerstats']['stats'] ?? null;
        if (!is_array($stats)) {
            throw new PrivateStatsException("Stats of $steamId are not available");
        }

        $result = [];
        foreach ($stats as $stat) {
            $result[(string) $stat['name']] = (int) $stat['value'];
        }

        return $result;
    }

    /**
     * Public profile summary (persona name, avatar, ...) of one player.
     *
     * @return array<string, mixed>|null null if the account does not exist
     */
    public function playerSummary(SteamId $steamId): ?array
    {
        $response = $this->http->get($this->url(self::URL_PLAYER_SUMMARIES, [
            'key' => $this->requireKey(),
            'steamids' => $steamId->value,
        ]));

        return $this->decode($response)['response']['players'][0] ?? null;
    }

    /**
     * All-time global totals.
     *
     * @param list<string> $names
     * @return array<string, int> stat name => total
     */
    public function globalTotals(array $names): array
    {
        $urls = array_map(fn (array $chunk): string => $this->globalUrl($chunk), array_chunk($names, self::GLOBAL_MAX_NAMES));

        $totals = [];
        foreach ($this->fetchGlobal($urls) as $stats) {
            foreach ($stats as $name => $stat) {
                $totals[$name] = (int) $stat['total'];
            }
        }

        return $totals;
    }

    /**
     * Daily global history for one or more inclusive UTC day windows.
     * Windows longer than GLOBAL_MAX_DAYS are split; all requests run in parallel.
     *
     * @param list<string> $names
     * @param list<array{0: int, 1: int}> $windows [first day, last day] as UTC midnight timestamps
     * @return array<string, array<int, int>> stat name => [day timestamp => count]
     */
    public function globalHistory(array $names, array $windows): array
    {
        $urls = [];
        foreach ($windows as [$first, $last]) {
            for ($start = $first; $start <= $last; $start += self::GLOBAL_MAX_DAYS * 86400) {
                $end = min($last, $start + (self::GLOBAL_MAX_DAYS - 1) * 86400);
                foreach (array_chunk($names, self::GLOBAL_MAX_NAMES) as $chunk) {
                    $urls[] = $this->globalUrl($chunk, $start, $end);
                }
            }
        }

        $history = array_fill_keys($names, []);
        foreach ($this->fetchGlobal($urls) as $stats) {
            foreach ($stats as $name => $stat) {
                foreach ($stat['history'] ?? [] as $day) {
                    $history[$name][(int) $day['date']] = (int) $day['total'];
                }
            }
        }

        return $history;
    }

    /** Download a binary resource (e.g. an avatar), null on failure. */
    public function download(string $url): ?string
    {
        $response = $this->http->get($url);

        return $response->ok() && $response->body !== '' ? $response->body : null;
    }

    /**
     * @param list<string> $urls
     * @return list<array<string, array<string, mixed>>> "globalstats" object of every response
     */
    private function fetchGlobal(array $urls): array
    {
        $result = [];
        foreach ($this->http->getMany($urls) as $response) {
            $body = $this->decode($response)['response'] ?? [];
            if (($body['result'] ?? null) !== 1) {
                throw new SteamApiException('GetGlobalStatsForGame failed: ' . ($body['error'] ?? 'unknown error'));
            }
            $result[] = $body['globalstats'] ?? [];
        }

        return $result;
    }

    /** @param list<string> $names */
    private function globalUrl(array $names, ?int $startDate = null, ?int $endDate = null): string
    {
        $query = ['appid' => self::APP_ID, 'count' => count($names), 'name' => array_values($names)];
        if ($startDate !== null && $endDate !== null) {
            $query['startdate'] = $startDate;
            $query['enddate'] = $endDate;
        }

        return $this->url(self::URL_GLOBAL_STATS, $query);
    }

    /** @param array<string, mixed> $query */
    private function url(string $base, array $query): string
    {
        // Steam expects "name[0]=..&name[1]=..", which is exactly what http_build_query produces.
        return $base . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    /** @return array<string, mixed> */
    private function decode(HttpResponse $response): array
    {
        if ($response->error !== null) {
            throw new SteamApiException("Steam API request failed: {$response->error}");
        }
        if (!$response->ok()) {
            throw new SteamApiException("Steam API returned HTTP {$response->status}");
        }
        $data = json_decode($response->body, true);
        if (!is_array($data)) {
            throw new SteamApiException('Steam API returned invalid JSON');
        }

        return $data;
    }

    private function requireKey(): string
    {
        if ($this->apiKey === '') {
            throw new SteamApiException('Steam API key is not configured (config.php or OJ_STEAM_API_KEY)');
        }

        return $this->apiKey;
    }
}
