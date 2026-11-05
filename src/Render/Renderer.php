<?php

declare(strict_types=1);

namespace OJStats\Render;

interface Renderer
{
    /** @return string PNG image bytes */
    public function render(RenderInput $input): string;
}
