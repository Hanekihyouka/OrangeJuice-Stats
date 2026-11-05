<?php

declare(strict_types=1);

namespace OJStats\Stats;

/**
 * Result of a global stats query.
 */
final class GlobalStats
{
    /**
     * @param list<CharacterRecord> $characters
     * @param DateRange|null $range null = all time
     */
    public function __construct(
        public readonly array $characters,
        public readonly ?DateRange $range,
        public readonly int $cachedDays,
        public readonly int $liveDays,
    ) {
    }

    public function total(): CharacterRecord
    {
        return CharacterRecord::total($this->characters);
    }
}
