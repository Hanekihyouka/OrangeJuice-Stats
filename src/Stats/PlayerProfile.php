<?php

declare(strict_types=1);

namespace OJStats\Stats;

use OJStats\Steam\SteamId;

final class PlayerProfile
{
    public function __construct(
        public readonly SteamId $steamId,
        public readonly string $name,
        public readonly string $avatarUrl,
    ) {
    }

    /** @param array<string, mixed> $summary a player object of GetPlayerSummaries */
    public static function fromSummary(SteamId $steamId, array $summary): self
    {
        return new self($steamId, (string) ($summary['personaname'] ?? $steamId), (string) ($summary['avatarfull'] ?? ''));
    }
}
