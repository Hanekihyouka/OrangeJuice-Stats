<?php

declare(strict_types=1);

/**
 * Image API: a player's stats rendered as PNG.
 *
 * Query:
 *   steamid     SteamID64 (required)
 *   render      style id, default 0
 *   limit       number of character rows (renders 0, 6, 8)
 *   nick        nickname override
 *   sp1         pin badge id (nickname colour for renders 1 and 2)
 *   hyper_icon  hyper card icon override (render 7)
 *   y_offset    hyper card crop offset override, negative mirrors it (render 7)
 */

use OJStats\Render\RenderInput;
use OJStats\Steam\PrivateStatsException;
use OJStats\Steam\SteamApiException;
use OJStats\Steam\SteamId;

/** @var \OJStats\App $app */
$app = require __DIR__ . '/../src/bootstrap.php';

/** Plain text error response. */
$fail = static function (int $status, string $message): never {
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message, "\n";
    exit;
};

/** Integer query parameter, null when absent or not an integer. */
$intParam = static function (string $name): ?int {
    $value = filter_var($_GET[$name] ?? null, FILTER_VALIDATE_INT);

    return $value === false ? null : $value;
};

$steamId = SteamId::tryParse($_GET['steamid'] ?? null) ?? $fail(400, 'Missing or invalid steamid (SteamID64).');
$renderers = $app->renderers();
$renderId = isset($_GET['render']) ? $intParam('render') : 0;
$renderer = ($renderId !== null ? $renderers->get($renderId) : null)
    ?? $fail(400, 'Unknown render, valid values: ' . implode(', ', $renderers->ids()) . '.');

try {
    $stats = $app->players()->stats($steamId);
    $profile = $app->players()->profile($steamId);
} catch (PrivateStatsException) {
    $fail(404, "The game details of $steamId are not public.");
} catch (SteamApiException $e) {
    $fail(502, 'Steam API error: ' . $e->getMessage());
}

$assets = $renderers->assets;
$nick = trim((string) ($_GET['nick'] ?? ''));
$pinId = max(0, $intParam('sp1') ?? 0);

$input = new RenderInput(
    profile: $profile,
    stats: $stats,
    avatar: ($profile->avatarUrl !== '' ? $app->steam()->download($profile->avatarUrl) : null)
        ?? (string) file_get_contents($assets->fallbackAvatar()),
    limit: $intParam('limit'),
    nick: $nick !== '' ? mb_substr($nick, 0, 64) : null,
    pinId: $pinId,
    pin: $assets->pin($pinId),
    hyperIcon: isset($_GET['hyper_icon']) ? $assets->hyperIcon((string) $_GET['hyper_icon']) : null,
    hyperOffsetY: $intParam('y_offset'),
);

$png = $renderer->render($input);

header('Content-Type: image/png');
header('Content-Length: ' . strlen($png));
header('Cache-Control: public, max-age=300');
echo $png;
