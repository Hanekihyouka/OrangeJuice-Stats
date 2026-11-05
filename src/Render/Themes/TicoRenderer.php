<?php

declare(strict_types=1);

namespace OJStats\Render\Themes;

use OJStats\Game\CoopRole;
use OJStats\Render\BaseRenderer;
use OJStats\Render\RenderInput;

/**
 * render=3: Tico themed card.
 */
final class TicoRenderer extends BaseRenderer
{
    /** Positions of the co-op role levels on the background artwork. */
    private const ROLE_POSITIONS = [
        'Attacker' => [580, 100], 'Guardian' => [610, 140], 'Support' => [610, 179],
        'Avenger' => [610, 210], 'Dealer' => [610, 253],
    ];

    public function render(RenderInput $input): string
    {
        $stats = $input->stats;
        $font = $this->assets->font('ARHei.ttf');

        $im = self::gdLoad($this->assets->theme('tico', 'Tico.png'));
        self::gdCopy($im, $this->gdAvatar($input), 60, 90);
        if ($input->pin !== null) {
            self::gdCopy($im, imagescale(self::gdLoad($input->pin), 54, 54), 200, 60);
        }

        $black = imagecolorallocate($im, 0, 0, 0);
        imagefttext($im, 30, 0, 450, 40, $black, $font, $input->nick());
        imagefttext($im, 20, 0, 670, 30, $black, $font, (string) $stats->level());

        foreach (CoopRole::cases() as $role) {
            if ([$x, $y] = self::ROLE_POSITIONS[$role->name] ?? null) {
                imagefttext($im, 20, 0, $x, $y, $black, $font, (string) $stats->coopLevel($role));
            }
        }

        imagefttext($im, 20, 0, 530, 300, $black, $font, (string) $stats->onlineGamesWon());
        imagefttext($im, 20, 0, 540, 330, $black, $font, self::percent($stats->characterTotal()->winRate()) . '%');
        imagefttext($im, 20, 0, 515, 360, $black, $font,
            "{$stats->bountyGamesWon()}/{$stats->bountyGamesPlayed()}  =  " . self::percent($stats->bountyWinRate()) . '%');

        return self::gdEncode($im, $input->format);
    }
}
