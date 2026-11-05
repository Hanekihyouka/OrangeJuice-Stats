<?php

declare(strict_types=1);

namespace OJStats\Render;

interface Renderer
{
    /** @return string image bytes, encoded as $input->format */
    public function render(RenderInput $input): string;
}
