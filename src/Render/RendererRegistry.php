<?php

declare(strict_types=1);

namespace OJStats\Render;

use OJStats\Game\GameData;
use OJStats\Render\Themes\AprilFools2023Renderer;
use OJStats\Render\Themes\AstralPartyRenderer;
use OJStats\Render\Themes\ClassicRenderer;
use OJStats\Render\Themes\MaimaiDxRenderer;
use OJStats\Render\Themes\MoriRenderer;
use OJStats\Render\Themes\TentekiKoRenderer;
use OJStats\Render\Themes\TicoRenderer;

/**
 * Maps the public "render" ids to renderer implementations.
 */
final class RendererRegistry
{
    /** @var array<int, \Closure(): Renderer> */
    private array $factories;

    public function __construct(
        public readonly Assets $assets,
        GameData $game,
    ) {
        $this->factories = [
            0 => fn () => new ClassicRenderer($assets, $game),
            1 => fn () => new MoriRenderer($assets, $game, sheared: true),
            2 => fn () => new MoriRenderer($assets, $game, sheared: false),
            3 => fn () => new TicoRenderer($assets, $game),
            4 => fn () => new TentekiKoRenderer($assets, $game, withUnit: false),
            5 => fn () => new TentekiKoRenderer($assets, $game, withUnit: true),
            6 => fn () => new AprilFools2023Renderer($assets, $game),
            7 => fn () => new AstralPartyRenderer($assets, $game),
            8 => fn () => new MaimaiDxRenderer($assets, $game),
        ];
    }

    /** @return list<int> */
    public function ids(): array
    {
        return array_keys($this->factories);
    }

    public function get(int $id): ?Renderer
    {
        return isset($this->factories[$id]) ? ($this->factories[$id])() : null;
    }
}
