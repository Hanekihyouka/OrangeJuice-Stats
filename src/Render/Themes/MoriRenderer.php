<?php

declare(strict_types=1);

namespace OJStats\Render\Themes;

use OJStats\Game\GameData;
use OJStats\Render\Assets;
use OJStats\Render\BaseRenderer;
use OJStats\Render\RenderInput;

/**
 * render=1 (sheared) / render=2 (straight): frosted glass panels over the blurred avatar.
 */
final class MoriRenderer extends BaseRenderer
{
    private const WIDTH = 760;
    private const HEIGHT = 400;
    private const PANEL_COLOR = 'rgba(20,20,20,0.4)';
    private const SHEAR = 15;
    private const ROLE_COLORS = [
        'Attacker' => '#F08080', 'Guardian' => '#87CEEB', 'Support' => '#98FB98',
        'Avenger' => '#FF8C00', 'Dealer' => '#BA55D3', 'Vanguard' => '#FFD700',
    ];
    /** sp1 => nickname colour */
    private const NICK_COLORS = [1 => '#F4A460', 2 => '#98FB98', 3 => '#87CEEB', 4 => '#BA55D3'];

    /** Text positions [x, y] of both layouts. */
    private const LAYOUTS = [
        'sheared' => [
            'level' => [385, 137], 'played' => [508, 92], 'won' => [500, 157],
            'coop' => [370, 234], 'bh' => [520, 234], 'roles' => [300, 330],
            'levelTitle' => [385, 96], 'playedTitle' => [555, 110], 'wonTitle' => [537, 175],
            'rolesTitle' => [430, 290], 'coopTitle' => [415, 248], 'bhTitle' => [565, 248],
        ],
        'straight' => [
            'level' => [415, 137], 'played' => [520, 92], 'won' => [520, 157],
            'coop' => [415, 234], 'bh' => [565, 234], 'roles' => [375, 330],
            'levelTitle' => [415, 96], 'playedTitle' => [580, 110], 'wonTitle' => [537, 175],
            'rolesTitle' => [505, 290], 'coopTitle' => [460, 248], 'bhTitle' => [610, 248],
        ],
    ];

    public function __construct(Assets $assets, GameData $game, private readonly bool $sheared)
    {
        parent::__construct($assets, $game);
    }

    public function render(RenderInput $input): string
    {
        $stats = $input->stats;
        $at = self::LAYOUTS[$this->sheared ? 'sheared' : 'straight'];

        // blurred, darkened avatar as background
        $avatar = $this->imagickAvatar($input);
        $im = clone $avatar;
        $im->cropImage(175, 100, 4, 42);
        $im->blurImage(5, 3);
        $im->adaptiveResizeImage(self::WIDTH, self::HEIGHT);
        $tint = new \Imagick();
        $tint->newImage(self::WIDTH, self::HEIGHT, new \ImagickPixel('rgba(60,60,60,0.7)'));
        self::imagickComposite($im, $tint, 0, 0);

        // avatar with drop shadow
        $avatar->setImageFormat('png');
        $avatar->adaptiveResizeImage(300, 300);
        if ($this->sheared) {
            $mask = new \Imagick();
            $mask->newImage(220, 300, new \ImagickPixel('rgba(0,0,0,1)'));
            $mask->setImageFormat('png');
            $mask->shearImage(new \ImagickPixel('rgba(255,255,255,0)'), self::SHEAR, 0);
            $avatar->compositeImage($mask, \Imagick::COMPOSITE_COPYOPACITY, 0, 0);
        }
        $im->compositeImage(self::withShadow($avatar, 250), \Imagick::COMPOSITE_DEFAULT, $this->sheared ? 40 : 50, 50);

        // stat panels; sheared panels shift left as they go down
        $shift = static fn (int $dy): float => $dy * tan(deg2rad(self::SHEAR));
        foreach ([[50, 130], [200, 50], [270, 80]] as [$y, $h]) {
            $x = $this->sheared ? 330 - $shift($y + $h - 180) : 370;
            self::panel($im, $x, $y, 360, $h, $this->sheared ? self::SHEAR : 0);
        }

        $text = self::imagickLayer(self::WIDTH, self::HEIGHT);
        $draw = new \ImagickDraw();
        $draw->setFillColor(new \ImagickPixel('#fff'));
        $draw->setFont($this->assets->font('SAO.otf'));
        $draw->setFontSize(45);
        $draw->annotation(...[...$at['level'], (string) $stats->level()]);
        $draw->annotation(...[...$at['played'], (string) $stats->onlineGamesPlayed()]);
        $draw->annotation(...[...$at['won'], (string) $stats->onlineGamesWon()]);

        $draw->setFontSize(30);
        $draw->annotation(...[...$at['coop'], (string) $stats->coopGamesPlayed()]);
        $draw->annotation(...[...$at['bh'], (string) $stats->bountyGamesPlayed()]);

        $draw->setFontSize(37);
        $this->drawCoopLevels($draw, $input, $at['roles'][0], $at['roles'][1], 60, self::ROLE_COLORS);

        $draw->setFillColor(new \ImagickPixel('#bbb'));
        $draw->setFontSize(17);
        $draw->annotation(...[...$at['levelTitle'], 'Level']);
        $draw->annotation(...[...$at['playedTitle'], 'Online  Games  Played']);
        $draw->annotation(...[...$at['wonTitle'], 'Online  Games  Won']);
        $draw->annotation(...[...$at['rolesTitle'], 'Coop Role Levels']);
        $draw->annotation(...[...$at['coopTitle'], 'Coop Games Played']);
        $draw->annotation(...[...$at['bhTitle'], 'BH Games Played']);

        // nickname on its own panel, shrunk until it fits
        $nick = $input->nick();
        $draw->setFont($this->assets->font('ARHei.ttf'));
        $draw->setFillColor(new \ImagickPixel(self::NICK_COLORS[$input->pinId] ?? '#fff'));
        $size = 35;
        $draw->setFontSize($size);
        $nickWidth = $text->queryFontMetrics($draw, $nick)['textWidth'];
        while ($nickWidth > 600) {
            $draw->setFontSize($size--);
            $nickWidth = $text->queryFontMetrics($draw, $nick)['textWidth'];
        }
        self::panel($im, -20, 12, (int) $nickWidth + 26, 41, self::SHEAR);
        $draw->annotation(4, 47, $nick);

        $text->drawImage($draw);
        self::imagickComposite($im, $text, 0, 0);

        return self::imagickEncode($im, $input->format);
    }

    /** Semi transparent panel with drop shadow, optionally sheared. */
    private static function panel(\Imagick $im, float $x, int $y, int $width, int $height, int $shear): void
    {
        $glass = new \Imagick();
        $glass->newImage($width, $height, new \ImagickPixel(self::PANEL_COLOR));
        if ($shear !== 0) {
            $glass->shearImage(new \ImagickPixel('rgba(0,0,0,0)'), $shear, 0);
        }
        $im->compositeImage(self::withShadow($glass, 160), \Imagick::COMPOSITE_DEFAULT, (int) $x, $y);
    }

    private static function withShadow(\Imagick $image, int $opacity): \Imagick
    {
        $shadow = clone $image;
        $shadow->setImageBackgroundColor(new \ImagickPixel('#555'));
        $shadow->shadowImage($opacity, 2, 0, 0);
        $shadow->compositeImage($image, \Imagick::COMPOSITE_OVER, 0, 0);

        return $shadow;
    }
}
