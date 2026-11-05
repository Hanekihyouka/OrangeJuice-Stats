<?php

declare(strict_types=1);

namespace OJStats\Stats;

/**
 * A player's profile and stats as of one Steam query.
 */
final class Player
{
    public function __construct(
        public readonly PlayerProfile $profile,
        public readonly PlayerStats $stats,
        /** Unix time of the Steam query the data comes from. */
        public readonly int $fetchedAt,
        /** Whether the data was served from the player cache. */
        public readonly bool $fromCache,
    ) {
    }
}
