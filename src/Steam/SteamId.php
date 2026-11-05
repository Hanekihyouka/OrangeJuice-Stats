<?php

declare(strict_types=1);

namespace OJStats\Steam;

/**
 * A validated 64 bit Steam ID (SteamID64), e.g. 76561198000000000.
 */
final class SteamId implements \Stringable
{
    private function __construct(public readonly string $value)
    {
    }

    /**
     * Accepts a bare SteamID64 or a profile URL such as
     * https://steamcommunity.com/profiles/76561198000000000/
     */
    public static function tryParse(?string $input): ?self
    {
        if ($input === null) {
            return null;
        }
        if (preg_match('/(?<![0-9])(7656119[0-9]{10})(?![0-9])/', trim($input), $match) !== 1) {
            return null;
        }

        return new self($match[1]);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
