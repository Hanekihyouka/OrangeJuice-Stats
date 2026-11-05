<?php

declare(strict_types=1);

namespace OJStats\Http;

final class HttpResponse
{
    public function __construct(
        public readonly string $url,
        public readonly int $status,
        public readonly string $body,
        /** Transport level error (DNS, timeout, ...), null on success. */
        public readonly ?string $error = null,
    ) {
    }

    public function ok(): bool
    {
        return $this->error === null && $this->status >= 200 && $this->status < 300;
    }
}
