<?php

declare(strict_types=1);

namespace OJStats\Stats;

/**
 * Games played / won with one character (by a player or globally).
 */
final class CharacterRecord
{
    public function __construct(
        public readonly string $name,
        public readonly int $games,
        public readonly int $wins,
    ) {
    }

    /** Win rate in [0, 1]; 0 when no games were played. */
    public function winRate(): float
    {
        return $this->games > 0 ? $this->wins / $this->games : 0.0;
    }

    /**
     * Sort by games, then wins, both descending. Stable for equal records.
     *
     * @param list<self> $records
     * @return list<self>
     */
    public static function sortByGames(array $records): array
    {
        usort($records, static fn (self $a, self $b): int => [$b->games, $b->wins] <=> [$a->games, $a->wins]);

        return $records;
    }

    /**
     * @param list<self> $records
     */
    public static function total(array $records, string $name = 'Total'): self
    {
        return new self(
            $name,
            array_sum(array_map(static fn (self $r): int => $r->games, $records)),
            array_sum(array_map(static fn (self $r): int => $r->wins, $records)),
        );
    }
}
