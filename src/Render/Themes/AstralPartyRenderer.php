<?php

declare(strict_types=1);

namespace OJStats\Render\Themes;

use OJStats\Render\BaseRenderer;
use OJStats\Render\RenderInput;

/**
 * render=7: Astral Party style profile card with the five most played characters.
 *
 * Options: hyper_icon / y_offset override the hyper card shown behind the nickname.
 */
final class AstralPartyRenderer extends BaseRenderer
{
    private const WIDTH = 500;
    private const HEIGHT = 576;
    private const SLOTS = 5;
    private const SLOT_STEP = 93;
    private const ROLE_COLORS = [
        'Attacker' => '#F08080', 'Guardian' => '#87CEEB', 'Support' => '#3CB371',
        'Avenger' => '#F4A460', 'Dealer' => '#BA55D3',
    ];

    public function render(RenderInput $input): string
    {
        $stats = $input->stats;

        $im = new \Imagick($this->assets->theme('astral-party', 'back.png'));
        $im->roundCornersImage(4, 4);
        if ($input->pin !== null) {
            $pin = new \Imagick($input->pin);
            $pin->resizeImage(35, 35, \Imagick::FILTER_UNDEFINED, 1, true);
            self::imagickComposite($im, $pin, 360, 55);
        }

        $text = self::imagickLayer(self::WIDTH, self::HEIGHT);
        $draw = new \ImagickDraw();
        $clash = $this->assets->font('Clash_Regular_Final.ttf');

        $draw->setFont($clash);
        $draw->setFontSize(17);
        self::outlined($draw, 'rgba(110, 110, 110, 0.5)', 131, 394, $input->nick());

        $draw->setFillColor(new \ImagickPixel('#666'));
        $draw->setFontSize(12);
        $draw->annotation(35, 423, gmdate('Y-m-d'));
        $draw->setFont($this->assets->font('SgaSmoothRegular-DO0Y3.ttf'));
        $draw->annotation(185, 423, self::glyphId((string) $input->profile->steamId));

        $draw->setFont($clash);
        $draw->setFillColor(new \ImagickPixel('#000'));
        $draw->setFontSize(12);
        $draw->annotation(131, 370, 'Lv.');
        $draw->setFontSize(19);
        $draw->annotation(143, 113, 'Wins');
        $draw->annotation(143, 156, 'Played');
        $draw->annotation(143, 199, 'Coop');
        $draw->annotation(143, 242, 'BH Wins');

        $this->drawCharacterSlots($im, $draw, $input);
        $this->drawMainCharacter($im, $input);

        $avatar = $this->imagickAvatar($input);
        $avatar->roundCornersImage(50, 50);
        $avatar->resizeImage(76, 76, \Imagick::FILTER_UNDEFINED, 1, true);
        self::imagickComposite($im, $avatar, 53, 329);

        $draw->setFontSize(19);
        $this->drawCoopLevels($draw, $input, 147, 285, 50, self::ROLE_COLORS);

        $draw->setFontSize(24);
        self::outlined($draw, 'rgba(110, 110, 110, 0.8)', 150, 370, (string) $stats->level());

        $draw->setTextAlignment(\Imagick::ALIGN_RIGHT);
        $draw->setFillColor(new \ImagickPixel('#666'));
        $draw->setFontSize(12);
        $draw->annotation(260, 113, '(' . self::percent($stats->characterTotal()->winRate(), 1) . '%)');
        $draw->annotation(290, 242, '(' . self::percent($stats->bountyWinRate(), 1) . '%)');
        $draw->setFillColor(new \ImagickPixel('#000'));
        $draw->setFontSize(19);
        $draw->annotation(360, 113, (string) $stats->onlineGamesWon());
        $draw->annotation(360, 156, (string) $stats->onlineGamesPlayed());
        $draw->annotation(360, 199, (string) $stats->coopGamesPlayed());
        $draw->annotation(360, 242, (string) $stats->bountyGamesWon());

        $text->drawImage($draw);
        self::imagickComposite($im, $text, 0, 0);

        return self::imagickEncode($im, $input->format);
    }

    /** The five most played characters: icon, games, wins and win rate. */
    private function drawCharacterSlots(\Imagick $im, \ImagickDraw $draw, RenderInput $input): void
    {
        $records = $this->topCharacters($input, self::SLOTS);
        for ($i = 0; $i < self::SLOTS; $i++) {
            $x = $i * self::SLOT_STEP;
            $record = $records[$i] ?? null;
            $icon = $record ? $this->assets->characterIcon($record->name) : null;
            if ($icon === null) {
                self::imagickComposite($im, new \Imagick($this->assets->theme('astral-party', 'empty_slot.png')), 26 + $x, 473);
                continue;
            }

            self::imagickComposite($im, new \Imagick($icon), 29 + $x, 447);
            $draw->setTextAlignment(\Imagick::ALIGN_RIGHT);
            $draw->setFontSize(12);
            $draw->annotation(105 + $x, 525, (string) $record->games);
            if ($record->wins !== 0) {
                $draw->setFontSize(19);
                $draw->annotation(100 + $x, 548, self::percent($record->winRate()) . '%');
                $draw->setFontSize(12);
                $draw->setTextAlignment(\Imagick::ALIGN_LEFT);
                $draw->annotation(25 + $x, 455, (string) $record->wins);
            }
            $draw->setTextAlignment(\Imagick::ALIGN_LEFT);
        }
    }

    /** Hyper card banner behind the nickname plus the unit artwork of the most played character. */
    private function drawMainCharacter(\Imagick $im, RenderInput $input): void
    {
        $main = $this->mainCharacter($input);
        if ($main === null || $this->assets->characterIcon($main->name) === null) {
            return;
        }

        if ($hyperFile = $input->hyperIcon ?? $this->assets->hyperIcon($main->hyperIcon)) {
            $hyper = new \Imagick($hyperFile);
            $offset = $input->hyperOffsetY ?? $main->hyperOffsetY;
            if ($offset < 0) {
                $offset = -$offset;
                $hyper->flopImage();
            }
            $offset = min($offset, max(0, $hyper->getImageHeight() - 76));
            $hyper->cropImage(237, 76, 19, $offset);
            $hyper->roundCorners(18, 18);
            self::imagickComposite($im, $hyper, 111, 329);
        }

        $unit = new \Imagick($this->assets->characterUnit($main));
        $unit->resizeImage(128, 128, \Imagick::FILTER_UNDEFINED, 1, true);
        self::imagickComposite($im, $unit, 332, 310);
    }

    private static function outlined(\ImagickDraw $draw, string $stroke, int $x, int $y, string $text): void
    {
        $draw->setFillColor(new \ImagickPixel('#000'));
        $draw->setStrokeColor(new \ImagickPixel($stroke));
        $draw->setStrokeWidth(1);
        $draw->annotation($x, $y, $text);
        $draw->setStrokeWidth(0);
        $draw->setStrokeColor('transparent');
    }

    /**
     * Decorative "alien glyph" id: the Steam ID in base 26, written with letters only
     * (rendered with the Standard Galactic Alphabet font).
     */
    private static function glyphId(string $steamId): string
    {
        $toLetters = static fn (string $number): string => strtr(
            base_convert($number, 10, 26),
            '0123456789abcdefghijklmnop',
            'abcdefghijklmnopqrstuvwxyz',
        );

        return $toLetters(substr($steamId, 0, 8)) . $toLetters(substr($steamId, 8));
    }
}
