<?php

declare(strict_types=1);

namespace OJStats\Stats;

use OJStats\Game\GameData;
use OJStats\Steam\SteamApi;
use OJStats\Steam\SteamApiException;
use OJStats\Steam\SteamId;

/**
 * Loads a single player's stats. Never cached: they change after every game.
 */
final class PlayerService
{
    public function __construct(
        private readonly SteamApi $steam,
        private readonly GameData $game,
    ) {
    }

    /**
     * @throws \OJStats\Steam\PrivateStatsException when the player's game details are not public
     * @throws SteamApiException on any other Steam API problem
     */
    public function stats(SteamId $steamId): PlayerStats
    {
        return PlayerStats::fromRaw($this->steam->playerStats($steamId), $this->game);
    }

    /** @throws SteamApiException */
    public function profile(SteamId $steamId): PlayerProfile
    {
        $summary = $this->steam->playerSummary($steamId);
        if ($summary === null) {
            throw new SteamApiException("Steam account $steamId does not exist");
        }

        return PlayerProfile::fromSummary($steamId, $summary);
    }
}
