<?php

declare(strict_types=1);

namespace OJStats\Stats;

use OJStats\Game\GameData;
use OJStats\Steam\SteamApi;
use OJStats\Steam\SteamApiException;
use OJStats\Steam\SteamId;

/**
 * Loads a single player's profile and stats.
 *
 * Successful Steam answers are kept in a per-player file cache for $cacheTtl seconds,
 * shared by the player page and the image API. Failures are never cached.
 */
final class PlayerService
{
    public function __construct(
        private readonly SteamApi $steam,
        private readonly GameData $game,
        private readonly PlayerCache $cache,
        /** Seconds cached data stays valid, 0 = always ask Steam. */
        private readonly int $cacheTtl,
    ) {
    }

    /**
     * @throws \OJStats\Steam\PrivateStatsException when the player's game details are not public
     * @throws SteamApiException on any other Steam API problem
     */
    public function get(SteamId $steamId): Player
    {
        if ($this->cacheTtl > 0 && $entry = $this->cache->get($steamId, $this->cacheTtl)) {
            return $this->build($steamId, $entry['stats'], $entry['summary'], $entry['fetched_at'], true);
        }

        $fetchedAt = time();
        $stats = $this->steam->playerStats($steamId);
        $summary = $this->steam->playerSummary($steamId)
            ?? throw new SteamApiException("Steam account $steamId does not exist");

        if ($this->cacheTtl > 0) {
            try {
                $this->cache->put($steamId, $fetchedAt, $stats, $summary);
            } catch (\Throwable $e) {
                error_log('OrangeJuice-Stats: ' . $e->getMessage()); // still serve the fresh data
            }
        }

        return $this->build($steamId, $stats, $summary, $fetchedAt, false);
    }

    /**
     * @param array<string, int> $stats
     * @param array<string, mixed> $summary
     */
    private function build(SteamId $steamId, array $stats, array $summary, int $fetchedAt, bool $fromCache): Player
    {
        return new Player(
            PlayerProfile::fromSummary($steamId, $summary),
            PlayerStats::fromRaw($stats, $this->game),
            $fetchedAt,
            $fromCache,
        );
    }
}
