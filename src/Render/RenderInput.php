<?php

declare(strict_types=1);

namespace OJStats\Render;

use OJStats\Stats\PlayerProfile;
use OJStats\Stats\PlayerStats;

/**
 * Everything a renderer needs: the player's data plus the user supplied options.
 */
final class RenderInput
{
    public function __construct(
        public readonly PlayerProfile $profile,
        public readonly PlayerStats $stats,
        /** Raw bytes of the avatar image (JPEG). */
        public readonly string $avatar,
        /** Number of character rows requested, null = renderer default. */
        private readonly ?int $limit = null,
        /** Nickname override. */
        private readonly ?string $nick = null,
        /** Raw "sp1" option: pin badge id, also picks the nickname colour of some renders. */
        public readonly int $pinId = 0,
        /** Pin badge image file, null if $pinId has no badge. */
        public readonly ?string $pin = null,
        /** Hyper icon file override. */
        public readonly ?string $hyperIcon = null,
        /** Hyper icon crop offset override. */
        public readonly ?int $hyperOffsetY = null,
    ) {
    }

    public function nick(): string
    {
        return $this->nick ?? $this->profile->name;
    }

    /** Requested row count, clamped to [0, number of characters played]. */
    public function limit(int $default): int
    {
        return max(0, min($this->limit ?? $default, count($this->stats->characters)));
    }
}
