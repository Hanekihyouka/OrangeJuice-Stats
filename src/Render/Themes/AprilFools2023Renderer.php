<?php

declare(strict_types=1);

namespace OJStats\Render\Themes;

use OJStats\Game\CoopRole;
use OJStats\Render\BaseRenderer;
use OJStats\Render\RenderInput;

/**
 * render=6: April Fools 2023 "hand written" card. Every text is randomly sized and tilted.
 */
final class AprilFools2023Renderer extends BaseRenderer
{
    private const WIDTH = 700;
    private const HEADER_HEIGHT = 400;
    private const ROW_HEIGHT = 72;
    private const ROLE_POSITIONS = [
        'Attacker' => [580, 145], 'Guardian' => [590, 215], 'Support' => [585, 290], 'Avenger' => [580, 350],
    ];

    private \GdImage $im;
    private string $font;

    public function render(RenderInput $input): string
    {
        $stats = $input->stats;
        $limit = $input->limit(5);
        $this->font = $this->assets->font('方正静蕾.ttf');

        $im = $this->im = imagecreatetruecolor(self::WIDTH, self::HEADER_HEIGHT + self::ROW_HEIGHT * ($limit + 1));
        imagesavealpha($im, true);
        $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $transparent);
        self::gdCopy($im, self::gdLoad($this->assets->theme('april-fools-2023', 'ap-render.png')), 0, 0);

        $avatar = imagerotate($this->gdAvatar($input), self::randomAngle(), $transparent);
        self::gdCopy($im, $avatar, 80, 110);
        if ($input->pin !== null) {
            self::gdCopy($im, imagescale(self::gdLoad($input->pin), 54, 54), 190, 60);
        }

        $black = imagecolorallocate($im, 0, 0, 0);
        $darkGray = imagecolorallocate($im, 36, 36, 36);
        $gray = imagecolorallocate($im, 127, 127, 127);

        $this->scribble(35, 230, 90, $black, $input->nick());
        foreach (CoopRole::cases() as $role) {
            if ([$x, $y] = self::ROLE_POSITIONS[$role->name] ?? null) {
                $this->scribble(30, $x, $y, $black, (string) $stats->coopLevel($role));
            }
        }

        $total = $stats->characterTotal();
        $this->scribble(35, 300, 200, $black, 'levels  ' . $stats->level());
        $this->scribble(30, 300, 215, $black, 'wins  ' . $stats->onlineGamesWon());
        $this->scribble(24, 100, 280, $darkGray, "pvp:  {$total->wins}/{$total->games}  =  " . self::percent($total->winRate()) . '%');
        $this->scribble(24, 100, 340, $darkGray,
            "bh:  {$stats->bountyGamesWon()}/{$stats->bountyGamesPlayed()}  =  " . self::percent($stats->bountyWinRate()) . '%');
        $this->scribble(24, 300, 275, $darkGray, 'online games:  ' . $stats->onlineGamesPlayed());
        $this->scribble(24, 300, 310, $darkGray, 'coop games:  ' . $stats->coopGamesPlayed());

        if ($limit > 0) {
            $this->scribble(24, 170, 390, $gray, 'games');
            $this->scribble(24, 290, 390, $gray, 'wins');
            $this->scribble(24, 390, 390, $gray, 'winrate %');
        }

        $rows = $this->topCharacters($input, $limit);
        foreach ($rows as $i => $record) {
            $top = self::HEADER_HEIGHT + $i * self::ROW_HEIGHT;
            self::gdCopy($im, self::gdLoad($this->assets->theme('april-fools-2023', 'INNER' . mt_rand(1, 5) . '.png')), 0, $top);
            if ($icon = $this->assets->characterIcon($record->name)) {
                self::gdCopy($im, self::gdLoad($icon), 53, $top);
            }
            $baseline = $top + 55;
            $this->scribble(26, 170, $baseline, $black, (string) $record->games);
            $this->scribble(26, 290, $baseline, $black, (string) $record->wins);
            $this->scribble(26, 390, $baseline, $black, self::percent($record->winRate()));
            $this->scribble(26, 500, $baseline, $black, $record->name);
        }
        self::gdCopy($im, self::gdLoad($this->assets->theme('april-fools-2023', 'END.png')), 0, self::HEADER_HEIGHT + count($rows) * self::ROW_HEIGHT);

        return self::gdPng($im);
    }

    /** Text with a random size (±8) and angle (±20°). */
    private function scribble(int $size, int $x, int $y, int $color, string $text): void
    {
        imagefttext($this->im, $size + mt_rand(-8, 8), self::randomAngle(), $x, $y, $color, $this->font, $text);
    }

    private static function randomAngle(): float
    {
        return mt_rand(-20_000, 20_000) / 1000;
    }
}
