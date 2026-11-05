<?php

declare(strict_types=1);

namespace OJStats;

use OJStats\Game\GameData;
use OJStats\Http\HttpClient;
use OJStats\Render\Assets;
use OJStats\Render\RendererRegistry;
use OJStats\Stats\DateRange;
use OJStats\Stats\GlobalStatsCache;
use OJStats\Stats\GlobalStatsService;
use OJStats\Stats\PlayerCache;
use OJStats\Stats\PlayerService;
use OJStats\Steam\SteamApi;

/**
 * Tiny service container. Services are created on first use.
 */
final class App
{
    /** @var array<string, object> */
    private array $services = [];

    private function __construct(
        public readonly string $root,
        public readonly Config $config,
    ) {
    }

    public static function boot(string $root): self
    {
        $root = realpath($root) ?: $root;
        date_default_timezone_set('UTC');

        return new self($root, Config::load($root));
    }

    public function game(): GameData
    {
        return $this->service(GameData::class, fn () => GameData::load($this->root . '/data'));
    }

    public function steam(): SteamApi
    {
        return $this->service(SteamApi::class, fn () => new SteamApi(
            new HttpClient($this->config->httpTimeout),
            $this->config->steamApiKey,
        ));
    }

    public function players(): PlayerService
    {
        return $this->service(PlayerService::class, fn () => new PlayerService(
            $this->steam(),
            $this->game(),
            new PlayerCache($this->config->playerCacheDir),
            $this->config->playerCacheTtl,
        ));
    }

    public function globalStats(): GlobalStatsService
    {
        return $this->service(GlobalStatsService::class, fn () => new GlobalStatsService(
            $this->steam(),
            new GlobalStatsCache($this->config->databasePath),
            $this->game(),
            DateRange::parseDay($this->config->earliestDate)
                ?? throw new \RuntimeException('Invalid earliest_date: ' . $this->config->earliestDate),
        ));
    }

    public function renderers(): RendererRegistry
    {
        return $this->service(RendererRegistry::class, fn () => new RendererRegistry(
            new Assets($this->root . '/public/images', $this->root . '/resources'),
            $this->game(),
        ));
    }

    public function view(): View
    {
        return $this->service(View::class, fn () => new View($this->root . '/templates', $this->baseUrl()));
    }

    /**
     * Public base URL of the site: the configured one, or derived from the current request.
     */
    public function baseUrl(): string
    {
        if ($this->config->baseUrl !== '') {
            return $this->config->baseUrl;
        }
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $https = ($_SERVER['HTTPS'] ?? '') !== '' && $_SERVER['HTTPS'] !== 'off'
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

        return ($https ? 'https' : 'http') . '://' . $host . $dir;
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @param callable(): T $factory
     * @return T
     */
    private function service(string $id, callable $factory): object
    {
        return $this->services[$id] ??= $factory();
    }
}
