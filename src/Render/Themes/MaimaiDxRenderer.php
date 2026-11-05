<?php

declare(strict_types=1);

namespace OJStats\Render\Themes;

use OJStats\Render\BaseRenderer;
use OJStats\Render\RenderInput;
use OJStats\Stats\CharacterRecord;

/**
 * render=8: maimai DX "best 50" style wall of character records, five per row.
 */
final class MaimaiDxRenderer extends BaseRenderer
{
    private const WIDTH = 1800;
    private const PER_ROW = 5;
    private const ROW_HEIGHT = 118;
    private const COLUMN_WIDTH = 353;
    /** Profile block anchor */
    private const PX = 612;
    private const PY = 76;
    private const ROLE_COLORS = [
        'Attacker' => '#E86D6D', 'Guardian' => '#5EB7DB', 'Support' => '#3CB371',
        'Avenger' => '#FF8C00', 'Dealer' => '#BA55D3', 'Vanguard' => '#FFD700',
    ];
    /** Rank badges: [win rate upper bound, image]; only for >= MIN_RANKED_GAMES games and > 25% win rate. */
    private const TIERS = [
        [.28, 'tierB'], [.30, 'tierBp'], [.35, 'tierA'], [.40, 'tierAp'],
        [.45, 'tierS'], [.50, 'tierSS'], [.80, 'tierSSS'], [INF, 'tierSSSp'],
    ];
    private const MIN_RANKED_GAMES = 20;
    private const MIN_RANKED_WIN_RATE = .25;
    private const FULL_COMBO_WIN_RATE = .97;

    public function render(RenderInput $input): string
    {
        $stats = $input->stats;
        $limit = $input->limit(50);
        $rows = (int) ceil($limit / self::PER_ROW);
        $height = 250 + $rows * self::ROW_HEIGHT;

        $im = new \Imagick($this->assets->theme('maimai-dx', 'back.png'));
        $im->cropImage(self::WIDTH, $height, 0, 0);
        $im->roundCornersImage(4, 4);

        $text = self::imagickLayer(self::WIDTH, $height);
        $draw = new \ImagickDraw();

        if ($rows > 0) {
            $draw->setFillColor('rgba(255, 255, 255, 0.55)');
            $draw->roundRectangle(self::PX - 210, self::PY - 57, self::PX + 790, self::PY + 147, 30, 30);
            $draw->roundRectangle(13, self::PY + 167, self::WIDTH - 13, $height - 9, 15, 15);
            $im->drawImage($draw);
            $draw->clear();
        }

        $draw->setFillColor(new \ImagickPixel('#000'));
        $draw->setFont($this->assets->font('ARHei.ttf'));
        $draw->setFontSize(72);
        $draw->annotation(self::PX, self::PY + 8, $input->nick());
        $draw->setFontSize(54);
        $draw->annotation(self::PX + 3, self::PY + 92, 'Lv.');
        $draw->setFontSize(48);
        $draw->annotation(self::PX + 415, self::PY + 6, 'Wins');
        $draw->annotation(self::PX + 415, self::PY + 51, 'Played');
        $draw->annotation(self::PX + 415, self::PY + 96, 'Coop');
        $draw->annotation(self::PX + 415, self::PY + 141, 'BH Wins');

        foreach ($this->topCharacters($input, $limit) as $index => $record) {
            $x = ($index % self::PER_ROW) * self::COLUMN_WIDTH - 50;
            $y = 241 + intdiv($index, self::PER_ROW) * self::ROW_HEIGHT;
            $this->drawSlot($im, $draw, $record, $x, $y);
        }

        $avatar = $this->imagickAvatar($input);
        $avatar->roundCornersImage(25, 25);
        self::imagickComposite($im, $avatar, self::PX - 200, self::PY - 47);

        $draw->setFontSize(40);
        $this->drawCoopLevels($draw, $input, self::PX, self::PY + 140, 70, self::ROLE_COLORS);
        $draw->setFillColor(new \ImagickPixel('#000'));
        $draw->setStrokeWidth(1);
        $draw->setFontSize(42);
        $draw->annotation(self::PX + 62, self::PY + 92, (string) $stats->level());

        $draw->setTextAlignment(\Imagick::ALIGN_RIGHT);
        $draw->setFillColor(new \ImagickPixel('#666'));
        $draw->setFontSize(32);
        $draw->annotation(self::PX + 665, self::PY + 2, '(' . self::percent($stats->characterTotal()->winRate(), 1) . '%)');
        $draw->annotation(self::PX + 665, self::PY + 137, '(' . self::percent($stats->bountyWinRate(), 1) . '%)');
        $draw->setFillColor(new \ImagickPixel('#000'));
        $draw->setFontSize(48);
        $draw->annotation(self::PX + 765, self::PY + 6, (string) $stats->onlineGamesWon());
        $draw->annotation(self::PX + 765, self::PY + 51, (string) $stats->onlineGamesPlayed());
        $draw->annotation(self::PX + 765, self::PY + 96, (string) $stats->coopGamesPlayed());
        $draw->annotation(self::PX + 765, self::PY + 141, (string) $stats->bountyGamesWon());

        $text->drawImage($draw);
        self::imagickComposite($im, $text, 0, 0);

        if ($input->pin !== null) {
            $pin = new \Imagick($input->pin);
            $pin->resizeImage(64, 64, \Imagick::FILTER_UNDEFINED, 1, true);
            self::imagickComposite($im, $pin, self::PX - 57, self::PY - 67);
        }

        return self::imagickEncode($im, $input->format);
    }

    private function drawSlot(\Imagick $im, \ImagickDraw $draw, CharacterRecord $record, int $x, int $y): void
    {
        // slot colour is picked from the name length, just for variety
        $slot = new \Imagick($this->assets->theme('maimai-dx', 'slot' . (strlen($record->name) % 4) . '.png'));
        self::imagickComposite($im, $slot, 64 + $x, $y);

        $character = $this->game->character($record->name);
        if ($character && $hyperFile = $this->assets->hyperIcon($character->hyperIcon)) {
            $hyper = new \Imagick($hyperFile);
            $hyper->resizeImage(96, 96, \Imagick::FILTER_UNDEFINED, 1, true);
            self::imagickComposite($im, $hyper, 80 + $x, $y + 12);
        }

        $draw->setFillColor(new \ImagickPixel('#fff'));
        $draw->setFontSize(32);
        $draw->setFont($this->assets->font('SAO.otf'));
        $draw->annotation($x + 200, $y + 38, $record->name);

        if ($record->wins !== 0) {
            $winRate = $record->winRate();
            $draw->annotation($x + 230, $y + 72, self::percent($winRate) . '%');
            $draw->setFillColor(new \ImagickPixel('#000'));
            $draw->setFontSize(22);
            $draw->annotation($x + 200, $y + 100, "{$record->wins} / {$record->games}");

            if ($tier = self::tier($record)) {
                self::imagickComposite($im, new \Imagick($this->assets->theme('maimai-dx', "$tier.png")), $x + 327, $y + 12);
            }
            if ($winRate > self::FULL_COMBO_WIN_RATE) {
                self::imagickComposite($im, new \Imagick($this->assets->theme('maimai-dx', 'fullcombo.png')), $x + 275, $y + 80);
            }
        } else {
            $draw->annotation($x + 230, $y + 72, ' ~ ');
            $draw->setFillColor(new \ImagickPixel('#000'));
            $draw->setFontSize(22);
            $draw->annotation($x + 200, $y + 100, " / {$record->games}");
        }

        if ($icon = $this->assets->characterIcon($record->name)) {
            self::imagickComposite($im, new \Imagick($icon), $x + 337, $y + 37);
        }
    }

    private static function tier(CharacterRecord $record): ?string
    {
        $winRate = $record->winRate();
        if ($record->games < self::MIN_RANKED_GAMES || $winRate <= self::MIN_RANKED_WIN_RATE) {
            return null;
        }
        foreach (self::TIERS as [$below, $tier]) {
            if ($winRate < $below) {
                return $tier;
            }
        }

        return null;
    }
}
