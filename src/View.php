<?php

declare(strict_types=1);

namespace OJStats;

/**
 * Renders plain PHP templates from templates/.
 *
 * Inside a template, $this is the View, so helpers are available as
 * $this->e(), $this->url(), $this->number(), $this->percent().
 */
final class View
{
    public function __construct(
        private readonly string $templateDir,
        private readonly string $baseUrl,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): string
    {
        $file = $this->templateDir . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new \InvalidArgumentException("Unknown template $template");
        }

        ob_start();
        try {
            (function () use ($file, $data): void {
                extract($data, EXTR_SKIP);
                require $file;
            })();

            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }

    /** HTML escape. */
    public function e(string|int|float|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * URL of a path below the public directory, relative to the current page
     * (every page lives in the public root).
     *
     * @param array<string, scalar> $query
     */
    public function url(string $path, array $query = []): string
    {
        $url = ltrim($path, '/');

        return $query === [] ? $url : $url . '?' . http_build_query($query);
    }

    /**
     * Absolute URL, for links meant to be copied and shared.
     *
     * @param array<string, scalar> $query
     */
    public function absoluteUrl(string $path, array $query = []): string
    {
        return $this->baseUrl . '/' . $this->url($path, $query);
    }

    public function number(int $value): string
    {
        return number_format($value);
    }

    public function percent(float $ratio, int $decimals = 2): string
    {
        return number_format(100 * $ratio, $decimals);
    }
}
