<?php

declare(strict_types=1);

namespace OJStats\Stats;

use OJStats\Game\CoopRole;
use OJStats\Game\GameData;

/**
 * In-game stats of one player, grouped for display.
 */
final class PlayerStats
{
    /**
     * @param array<string, int> $raw all stats as returned by Steam
     * @param list<CharacterRecord> $characters sorted by games played, descending
     * @param array<string, int> $cards card id => times chosen
     * @param array<string, int> $bounties npc id => times defeated
     * @param array<string, int> $others every other stat => value
     */
    private function __construct(
        private readonly array $raw,
        public readonly array $characters,
        public readonly array $cards,
        public readonly array $bounties,
        public readonly array $others,
    ) {
    }

    /** @param array<string, int> $raw */
    public static function fromRaw(array $raw, GameData $game): self
    {
        $characters = [];
        $cards = [];
        $bounties = [];
        $others = [];

        // Steam omits stats that were never set, so a character may only have a "games" stat.
        foreach ($raw as $stat => $value) {
            if ($found = $game->characterForStat($stat)) {
                [$character, $isWins] = $found;
                $characters[$character->name] ??= ['games' => 0, 'wins' => 0];
                $characters[$character->name][$isWins ? 'wins' : 'games'] = $value;
            } elseif (isset($game->cards[$stat])) {
                $cards[$game->cards[$stat]] = $value;
            } elseif (isset($game->bounties[$stat])) {
                $bounties[$game->bounties[$stat]] = $value;
            } else {
                $others[$stat] = $value;
            }
        }

        $records = [];
        foreach ($characters as $name => $counts) {
            $records[] = new CharacterRecord($name, $counts['games'], $counts['wins']);
        }

        return new self($raw, CharacterRecord::sortByGames($records), $cards, $bounties, $others);
    }

    public function get(string $stat): int
    {
        return $this->raw[$stat] ?? 0;
    }

    public function level(): int
    {
        return $this->get('STAT_PLAYER_LEVEL');
    }

    /** Progress towards the next player level, in [0, 1]. */
    public function levelProgress(): float
    {
        $level = $this->level();
        $expForNextLevel = (1300 + 100 * $level) * $level / 2;

        return 1 - ($expForNextLevel - $this->get('STAT_PLAYER_EXP')) / (600 + 100 * $level);
    }

    public function coopLevel(CoopRole $role): int
    {
        return CoopRole::levelFromExp($this->get($role->value));
    }

    /** Sum of all online games played / won, over all characters. */
    public function characterTotal(): CharacterRecord
    {
        return CharacterRecord::total($this->characters);
    }

    public function onlineGamesPlayed(): int
    {
        return $this->get('STAT_ONLINE_GAMES_PLAYED');
    }

    public function onlineGamesWon(): int
    {
        return $this->get('STAT_ONLINE_GAMES_WON');
    }

    public function bountyGamesPlayed(): int
    {
        return $this->get('STAT_ONLINE_BH_GAMES_PLAYED');
    }

    public function bountyGamesWon(): int
    {
        return $this->get('STAT_ONLINE_BH_GAMES_WON');
    }

    public function bountyWinRate(): float
    {
        return $this->bountyGamesPlayed() > 0 ? $this->bountyGamesWon() / $this->bountyGamesPlayed() : 0.0;
    }

    public function coopGamesPlayed(): int
    {
        return $this->get('STAT_C_GAMES_PLAYED');
    }

    public function npcDefeats(): int
    {
        return $this->get('STAT_NPC_DEFEATS');
    }
}
