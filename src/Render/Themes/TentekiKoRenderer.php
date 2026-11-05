<?php

declare(strict_types=1);

namespace OJStats\Render\Themes;

use OJStats\Game\GameData;
use OJStats\Render\Assets;
use OJStats\Render\BaseRenderer;
use OJStats\Render\RenderInput;

/**
 * render=4 (big avatar + face of the main character) /
 * render=5 (small avatar + unit artwork of the main character): compact banner.
 */
final class TentekiKoRenderer extends BaseRenderer
{
    private const WIDTH = 454;
    private const HEIGHT = 136;

    public function __construct(Assets $assets, GameData $game, private readonly bool $withUnit)
    {
        parent::__construct($assets, $game);
    }

    public function render(RenderInput $input): string
    {
        $stats = $input->stats;
        $hasPin = $input->pin !== null;

        $im = new \Imagick($this->assets->theme($this->withUnit ? 'tentekiko-unit' : 'tentekiko-face', 'back.png'));
        $im->roundCornersImage(4, 4);

        $avatar = $this->imagickAvatar($input);
        $avatarSize = $this->withUnit ? 32 : 121;
        $avatar->roundCornersImage($this->withUnit ? 24 : 4, $this->withUnit ? 24 : 4);
        $avatar->resizeImage($avatarSize, $avatarSize, \Imagick::FILTER_UNDEFINED, 1, true);
        self::imagickComposite($im, $avatar, 8, 8);

        if ($hasPin) {
            $pin = new \Imagick($input->pin);
            $pin->resizeImage(35, 35, \Imagick::FILTER_UNDEFINED, 1, true);
            self::imagickComposite($im, $pin, $this->withUnit ? 44 : 412, 7);
        }

        $main = $this->mainCharacter($input);
        if ($this->withUnit) {
            self::imagickComposite($im, new \Imagick($this->assets->characterUnit($main)), 250, -64);
        } else {
            $face = new \Imagick($this->assets->characterFace($main));
            $face->resizeImage(46, 46, \Imagick::FILTER_UNDEFINED, 1, true);
            self::imagickComposite($im, $face, $hasPin ? 360 : 402, 4);
        }

        // text block; numbers are right aligned at $right
        $left = $this->withUnit ? 12 : 143;
        $right = $this->withUnit ? 300 : 440;
        $nickX = $this->withUnit ? ($hasPin ? 80 : 48) : 137;

        $text = self::imagickLayer(self::WIDTH, self::HEIGHT);
        $draw = new \ImagickDraw();
        $draw->setFillColor(new \ImagickPixel('#fff'));
        $draw->setFont($this->assets->font('ResourceHanRoundedCN-Medium.ttf'));
        $draw->setFontSize(27);
        $draw->annotation($nickX, 34, $input->nick());

        $draw->setFont($this->assets->font('RockoUltraFLF.ttf'));
        $draw->setFillColor(new \ImagickPixel('#666'));
        $draw->setFontSize(19);
        $draw->annotation($left, 67, 'Level');
        $draw->annotation($left, 90, 'Online Wins');
        $draw->annotation($left, 113, 'Online Played');

        $draw->setTextAlignment(\Imagick::ALIGN_RIGHT);
        $draw->annotation($right, 67, '(' . self::percent($stats->levelProgress(), 1) . '%)');
        $draw->annotation($right - 85, 67, (string) $stats->level());
        $draw->annotation($right, 90, '(' . self::percent($stats->characterTotal()->winRate(), 1) . '%)');
        $draw->annotation($right - 85, 90, (string) $stats->onlineGamesWon());
        $draw->annotation($right, 113, (string) $stats->onlineGamesPlayed());

        $text->drawImage($draw);
        self::imagickComposite($im, $text, 0, 0);

        return self::imagickPng($im);
    }
}
