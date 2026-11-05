<?php

declare(strict_types=1);

namespace OJStats\Game;

final class Character
{
    public function __construct(
        public readonly string $name,
        public readonly string $gamesStat,
        public readonly string $winsStat,
        /** null = no face / unit artwork (falls back to a silhouette) */
        public readonly ?string $face,
        public readonly string $hyperIcon,
        public readonly int $hyperOffsetY,
    ) {
    }
}
