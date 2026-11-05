<?php

declare(strict_types=1);

namespace OJStats\Render\Themes;

use OJStats\Game\CoopRole;
use OJStats\Render\BaseRenderer;
use OJStats\Render\RenderInput;

/**
 * render=0: orange card with avatar, overview and a table of the most played characters.
 */
final class ClassicRenderer extends BaseRenderer
{
    private const WIDTH = 700;
    private const ROW_HEIGHT = 72;
    private const TABLE_TOP = 450;

    public function render(RenderInput $input): string
    {
        $stats = $input->stats;
        $limit = $input->limit(5);
        $height = 50 + 400 + self::ROW_HEIGHT * $limit + 50;
        $font = $this->assets->font('SAO.otf');
        $nickFont = $this->assets->font('SourceHanSansCN-Heavy-2.otf');

        $im = imagecreatetruecolor(self::WIDTH, $height);
        imagesavealpha($im, true);

        // tiled background
        $background = self::gdLoad($this->assets->image($stats->level() >= 300 ? 'background2.png' : 'background.png'));
        for ($y = 0; $y < $height; $y += imagesy($background)) {
            self::gdCopy($im, $background, -41, $y);
        }

        $orange = imagecolorallocate($im, 254, 207, 142);
        $black = imagecolorallocate($im, 0, 0, 0);
        $darkGray = imagecolorallocate($im, 36, 36, 36);
        $gray = imagecolorallocate($im, 127, 127, 127);
        $white = imagecolorallocate($im, 255, 255, 255);

        imagefilledrectangle($im, 50, 50, self::WIDTH - 50, $height - 50, $gray);   // frame
        imagefilledrectangle($im, 55, 55, self::WIDTH - 55, $height - 55, $orange); // card
        imagefilledrectangle($im, 97, 122, 286, 311, $gray);                       // avatar frame
        self::gdCopy($im, $this->gdAvatar($input), 100, 125);

        foreach (CoopRole::cases() as $i => $role) {
            self::gdCopy($im, self::gdLoad($this->assets->image("role$i.png")), 500, 100 + $i * 55);
            imagefttext($im, 27, 0, 570, 155 + $i * 55, $darkGray, $font, (string) $stats->coopLevel($role));
        }

        if ($input->pin !== null) {
            self::gdCopy($im, imagescale(self::gdLoad($input->pin), 96, 96), 530, 0);
        }

        // white background of every other table row
        for ($i = 0; $i < $limit; $i += 2) {
            $top = self::TABLE_TOP + self::ROW_HEIGHT * $i;
            $bottom = $top + self::ROW_HEIGHT - ($i === $limit - 1 ? 5 : 0);
            imagefilledrectangle($im, 55, $top, self::WIDTH - 55, $bottom, $white);
        }

        $nick = $input->nick();
        $nickWidth = imagettfbbox(30, 0, $nickFont, $nick)[2];
        imagefttext($im, 30, 0, (int) ((self::WIDTH - $nickWidth) / 2), 110, $black, $nickFont, $nick);

        imagefttext($im, 27, 0, 300, 165, $black, $font, 'lvl  ' . $stats->level());
        imagefttext($im, 27, 0, 300, 225, $black, $font, 'wins  ' . $stats->onlineGamesWon());

        $total = $stats->characterTotal();
        imagefttext($im, 24, 0, 100, 360, $darkGray, $font,
            "pvp:  {$total->wins}/{$total->games}  =  " . self::percent($total->winRate()) . '%');
        imagefttext($im, 24, 0, 100, 400, $darkGray, $font,
            "bh:  {$stats->bountyGamesWon()}/{$stats->bountyGamesPlayed()}  =  " . self::percent($stats->bountyWinRate()) . '%');
        imagefttext($im, 24, 0, 300, 275, $darkGray, $font, 'online games:  ' . $stats->onlineGamesPlayed());
        imagefttext($im, 24, 0, 300, 310, $darkGray, $font, 'coop games:  ' . $stats->coopGamesPlayed());

        if ($limit > 0) {
            imagefttext($im, 24, 0, 170, 445, $gray, $font, 'games');
            imagefttext($im, 24, 0, 290, 445, $gray, $font, 'wins');
            imagefttext($im, 24, 0, 390, 445, $gray, $font, 'winrate %');
        }

        foreach ($this->topCharacters($input, $limit) as $i => $record) {
            $top = self::TABLE_TOP + $i * self::ROW_HEIGHT;
            if ($icon = $this->assets->characterIcon($record->name)) {
                self::gdCopy($im, self::gdLoad($icon), 53, $top);
            }
            $baseline = $top + 55;
            imagefttext($im, 26, 0, 170, $baseline, $black, $font, (string) $record->games);
            imagefttext($im, 26, 0, 290, $baseline, $black, $font, (string) $record->wins);
            imagefttext($im, 26, 0, 390, $baseline, $black, $font, self::percent($record->winRate()));
            imagefttext($im, 26, 0, 500, $baseline, $black, $font, $record->name);
        }

        return self::gdPng($im);
    }
}
