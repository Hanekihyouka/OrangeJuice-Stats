<?php

declare(strict_types=1);

namespace OJStats\Render;

use OJStats\Game\Character;

/**
 * Locates image and font files used by the renderers.
 */
final class Assets
{
    /** Unit artwork file suffixes, tried in this order. */
    private const UNIT_SUFFIXES = [
        '_00_03', '_00_00', '_00_01',
        '_01_00_03', '_04_00_03', '_10_00_03', '_11_00_03', '_12_00_03', '_00_00_03',
    ];
    /** Face artwork file suffixes, tried in this order. */
    private const FACE_SUFFIXES = ['_00_03', '_00_00', '_00_01'];

    public function __construct(
        /** public/images */
        private readonly string $imageDir,
        /** resources/ */
        private readonly string $resourceDir,
    ) {
    }

    public function font(string $file): string
    {
        return $this->resourceDir . '/fonts/' . $file;
    }

    /** Image specific to one renderer, resources/renders/<theme>/<file>. */
    public function theme(string $theme, string $file): string
    {
        return $this->resourceDir . '/renders/' . $theme . '/' . $file;
    }

    /** Shared image below public/images. */
    public function image(string $file): string
    {
        return $this->imageDir . '/' . $file;
    }

    public function fallbackAvatar(): string
    {
        return $this->resourceDir . '/avatar-fallback.jpg';
    }

    /** Small character icon, null if there is none. */
    public function characterIcon(string $name): ?string
    {
        return $this->existing($this->image("chara/$name.png"));
    }

    public function characterFace(?Character $character): string
    {
        return $this->firstExisting('face', $character?->face, self::FACE_SUFFIXES) ?? $this->silhouette();
    }

    public function characterUnit(?Character $character): string
    {
        return $this->firstExisting('units', $character?->face, self::UNIT_SUFFIXES) ?? $this->silhouette();
    }

    public function hyperIcon(string $icon): ?string
    {
        return preg_match('/^[a-z0-9_]+$/i', $icon) === 1
            ? $this->existing($this->image("cards/hyper/{$icon}256.png"))
            : null;
    }

    /** "sp1" pin badge, null if there is no pin with that id. */
    public function pin(int $id): ?string
    {
        return $id > 0 ? $this->existing($this->image("sp1_$id.png")) : null;
    }

    private function silhouette(): string
    {
        return $this->image('units/silhouette_00.png');
    }

    /** @param list<string> $suffixes */
    private function firstExisting(string $dir, ?string $prefix, array $suffixes): ?string
    {
        if ($prefix === null || $prefix === '') {
            return null;
        }
        foreach ($suffixes as $suffix) {
            if ($file = $this->existing($this->image("$dir/$prefix$suffix.png"))) {
                return $file;
            }
        }

        return null;
    }

    private function existing(string $file): ?string
    {
        return is_file($file) ? $file : null;
    }
}
