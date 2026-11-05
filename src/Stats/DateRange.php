<?php

declare(strict_types=1);

namespace OJStats\Stats;

/**
 * Inclusive range of whole UTC days. Steam's global history is bucketed by UTC day.
 */
final class DateRange
{
    public const DAY = 86400;

    private function __construct(
        /** UTC midnight timestamp of the first day */
        public readonly int $first,
        /** UTC midnight timestamp of the last day */
        public readonly int $last,
    ) {
    }

    public static function of(int $first, int $last): self
    {
        $first = self::floorDay($first);
        $last = self::floorDay($last);
        if ($first > $last) {
            [$first, $last] = [$last, $first];
        }

        return new self($first, $last);
    }

    /** Parse "Y-m-d" strings, null when either is missing or malformed. */
    public static function tryParse(?string $first, ?string $last): ?self
    {
        $a = self::parseDay($first);
        $b = self::parseDay($last);

        return $a === null || $b === null ? null : self::of($a, $b);
    }

    public static function today(): int
    {
        return self::floorDay(time());
    }

    public static function floorDay(int $timestamp): int
    {
        return $timestamp - ((($timestamp % self::DAY) + self::DAY) % self::DAY);
    }

    public static function parseDay(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value, new \DateTimeZone('UTC'));

        return $date !== false && $date->format('Y-m-d') === $value ? $date->getTimestamp() : null;
    }

    public static function format(int $day): string
    {
        return gmdate('Y-m-d', $day);
    }

    /** Intersection with [$first, $last], null if empty. */
    public function clamp(int $first, int $last): ?self
    {
        $a = max($this->first, $first);
        $b = min($this->last, $last);

        return $a <= $b ? new self($a, $b) : null;
    }

    public function days(): int
    {
        return intdiv($this->last - $this->first, self::DAY) + 1;
    }

    /** @return list<int> UTC midnight timestamp of every day in the range */
    public function eachDay(): array
    {
        return range($this->first, $this->last, self::DAY);
    }

    public function firstString(): string
    {
        return self::format($this->first);
    }

    public function lastString(): string
    {
        return self::format($this->last);
    }
}
