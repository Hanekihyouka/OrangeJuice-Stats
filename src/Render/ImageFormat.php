<?php

declare(strict_types=1);

namespace OJStats\Render;

/**
 * Output format of the image API.
 */
enum ImageFormat: string
{
    case Webp = 'webp';
    case Png = 'png';

    /** Lossy WebP quality: visually identical to PNG for these renders at ~1/4 of the size. */
    public const WEBP_QUALITY = 90;

    public function mimeType(): string
    {
        return 'image/' . $this->value;
    }
}
