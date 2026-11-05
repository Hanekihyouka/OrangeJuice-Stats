<?php

declare(strict_types=1);

namespace OJStats\Game;

/**
 * Co-op roles. The backing value is the Steam stat holding the role's experience.
 */
enum CoopRole: string
{
    case Attacker = 'STAT_PLAYER_EXP2A';
    case Guardian = 'STAT_PLAYER_EXP2B';
    case Support = 'STAT_PLAYER_EXP2C';
    case Avenger = 'STAT_PLAYER_EXP2D';
    case Dealer = 'STAT_PLAYER_EXP2E';
    case Vanguard = 'STAT_PLAYER_EXP2F';

    public function label(): string
    {
        return match ($this) {
            self::Attacker => '攻击者',
            self::Guardian => '守护者',
            self::Support => '支援者',
            self::Avenger => '复仇者',
            self::Dealer => '发牌者',
            self::Vanguard => '先驱者',
        };
    }

    /** Index used by the icon files images/role<index>.png */
    public function index(): int
    {
        return array_search($this, self::cases(), true);
    }

    public static function levelFromExp(int $exp): int
    {
        return (int) floor((-11 + sqrt(289.0 + 2.4 * $exp)) / 6);
    }
}
