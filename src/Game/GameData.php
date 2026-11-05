<?php

declare(strict_types=1);

namespace OJStats\Game;

/**
 * Static game knowledge loaded from data/*.php.
 */
final class GameData
{
    /** @var list<Character> */
    private array $characters = [];
    /** @var array<string, Character> */
    private array $charactersByName = [];
    /** @var array<string, array{Character, bool}> stat => [character, isWinsStat] */
    private array $characterStats = [];

    /**
     * @param list<array<string, mixed>> $characters
     * @param array<string, string> $cards stat => card id
     * @param array<string, string> $bounties stat => npc id
     * @param array<string, string> $descriptions stat => description
     */
    public function __construct(
        array $characters,
        public readonly array $cards,
        public readonly array $bounties,
        private readonly array $descriptions,
    ) {
        foreach ($characters as $row) {
            $character = new Character(
                name: $row['name'],
                gamesStat: $row['games'],
                winsStat: $row['wins'],
                face: $row['face'],
                hyperIcon: $row['hyper'],
                hyperOffsetY: $row['hyperY'],
            );
            $this->characters[] = $character;
            $this->charactersByName[$character->name] = $character;
            $this->characterStats[$character->gamesStat] = [$character, false];
            $this->characterStats[$character->winsStat] = [$character, true];
        }
    }

    public static function load(string $dataDir): self
    {
        return new self(
            require $dataDir . '/characters.php',
            require $dataDir . '/cards.php',
            require $dataDir . '/bounties.php',
            require $dataDir . '/stat_descriptions.php',
        );
    }

    /** @return list<Character> */
    public function characters(): array
    {
        return $this->characters;
    }

    public function character(string $name): ?Character
    {
        return $this->charactersByName[$name] ?? null;
    }

    /** @return array{Character, bool}|null [character, isWinsStat] */
    public function characterForStat(string $stat): ?array
    {
        return $this->characterStats[$stat] ?? null;
    }

    public function describe(string $stat): string
    {
        return $this->descriptions[$stat] ?? '-';
    }
}
