<?php

declare(strict_types=1);

namespace OJStats\Render;

use OJStats\Game\Character;
use OJStats\Game\CoopRole;
use OJStats\Game\GameData;
use OJStats\Stats\CharacterRecord;

/**
 * Common helpers for all renderers.
 */
abstract class BaseRenderer implements Renderer
{
    public function __construct(
        protected readonly Assets $assets,
        protected readonly GameData $game,
    ) {
    }

    /** The player's most played character, if any. */
    protected function mainCharacter(RenderInput $input): ?Character
    {
        $record = $input->stats->characters[0] ?? null;

        return $record ? $this->game->character($record->name) : null;
    }

    /** @return list<CharacterRecord> */
    protected function topCharacters(RenderInput $input, int $count): array
    {
        return array_slice($input->stats->characters, 0, $count);
    }

    /**
     * Draw the level of each co-op role, left to right.
     *
     * @param array<string, string> $colors CoopRole name => colour; roles without colour are skipped
     */
    protected function drawCoopLevels(\ImagickDraw $draw, RenderInput $input, float $x, float $y, float $step, array $colors): void
    {
        $i = 0;
        foreach (CoopRole::cases() as $role) {
            if (!isset($colors[$role->name])) {
                continue;
            }
            $draw->setFillColor(new \ImagickPixel($colors[$role->name]));
            $draw->annotation($x + $step * $i++, $y, (string) $input->stats->coopLevel($role));
        }
    }

    /** "12.34" style percentage of a ratio, as the original renders printed it. */
    protected static function percent(float $ratio, int $precision = 2): string
    {
        return (string) round(100 * $ratio, $precision);
    }

    // ----- GD -----

    protected static function gdLoad(string $file): \GdImage
    {
        $image = imagecreatefromstring((string) file_get_contents($file));
        if ($image === false) {
            throw new \RuntimeException("Cannot load image $file");
        }

        return $image;
    }

    protected function gdAvatar(RenderInput $input): \GdImage
    {
        $image = @imagecreatefromstring($input->avatar);

        return $image !== false ? $image : self::gdLoad($this->assets->fallbackAvatar());
    }

    protected static function gdCopy(\GdImage $target, \GdImage $source, int $x, int $y): void
    {
        imagecopy($target, $source, $x, $y, 0, 0, imagesx($source), imagesy($source));
    }

    protected static function gdEncode(\GdImage $image, ImageFormat $format): string
    {
        ob_start();
        if ($format === ImageFormat::Webp) {
            imagepalettetotruecolor($image); // WebP needs a true colour image
            imagesavealpha($image, true);
            imagewebp($image, null, ImageFormat::WEBP_QUALITY);
        } else {
            imagepng($image);
        }

        return (string) ob_get_clean();
    }

    // ----- Imagick -----

    protected function imagickAvatar(RenderInput $input): \Imagick
    {
        $image = new \Imagick();
        try {
            $image->readImageBlob($input->avatar);
        } catch (\ImagickException) {
            $image = new \Imagick($this->assets->fallbackAvatar());
        }

        return $image;
    }

    /** Transparent layer to draw text on. */
    protected static function imagickLayer(int $width, int $height): \Imagick
    {
        $layer = new \Imagick();
        $layer->newImage($width, $height, 'none');
        $layer->setImageFormat('png');

        return $layer;
    }

    protected static function imagickComposite(\Imagick $target, \Imagick $source, int|float $x, int|float $y): void
    {
        $target->compositeImage($source, $source->getImageCompose(), (int) $x, (int) $y);
    }

    protected static function imagickEncode(\Imagick $image, ImageFormat $format): string
    {
        $image->setImageFormat($format->value);
        if ($format === ImageFormat::Webp) {
            $image->setImageCompressionQuality(ImageFormat::WEBP_QUALITY);
        }

        return $image->getImageBlob();
    }
}
