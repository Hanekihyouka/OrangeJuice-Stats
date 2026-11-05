<?php

declare(strict_types=1);

/**
 * Image API: a player's stats rendered as an image (WebP, or PNG with format=png).
 *
 * Query:
 *   steamid     SteamID64 (required)
 *   render      style id, default 0
 *   limit       number of character rows (renders 0, 6, 8)
 *   nick        nickname override
 *   sp1         pin badge id (nickname colour for renders 1 and 2)
 *   hyper_icon  hyper card icon override (render 7)
 *   y_offset    hyper card crop offset override, negative mirrors it (render 7)
 *   format      "png" for PNG output; default is WebP
 */

use OJStats\Render\ImageFormat;
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

/** String query parameter, null when absent or not a string (e.g. "nick[]=..."). */
$strParam = static function (string $name): ?string {
    $value = $_GET[$name] ?? null;

    return is_string($value) ? $value : null;
};

$steamId = SteamId::tryParse($strParam('steamid')) ?? $fail(400, 'Missing or invalid steamid (SteamID64).');
$renderers = $app->renderers();
$renderId = isset($_GET['render']) ? $intParam('render') : 0;
$renderer = ($renderId !== null ? $renderers->get($renderId) : null)
    ?? $fail(400, 'Unknown render, valid values: ' . implode(', ', $renderers->ids()) . '.');

$format = ImageFormat::tryFrom(strtolower(isset($_GET['format']) ? ($strParam('format') ?? '') : ImageFormat::Webp->value))
    ?? $fail(400, 'Unknown format, valid values: ' . implode(', ', array_column(ImageFormat::cases(), 'value')) . '.');

try {
    $player = $app->players()->get($steamId);
} catch (PrivateStatsException) {
    $fail(404, "The game details of $steamId are not public.");
} catch (SteamApiException $e) {
    $fail(502, 'Steam API error: ' . $e->getMessage());
}

$assets = $renderers->assets;
$nick = trim($strParam('nick') ?? '');
$pinId = max(0, $intParam('sp1') ?? 0);

$input = new RenderInput(
    profile: $player->profile,
    stats: $player->stats,
    avatar: ($player->profile->avatarUrl !== '' ? $app->steam()->download($player->profile->avatarUrl) : null)
        ?? (string) file_get_contents($assets->fallbackAvatar()),
    limit: $intParam('limit'),
    nick: $nick !== '' ? mb_substr($nick, 0, 64) : null,
    pinId: $pinId,
    pin: $assets->pin($pinId),
    hyperIcon: ($icon = $strParam('hyper_icon')) !== null ? $assets->hyperIcon($icon) : null,
    hyperOffsetY: $intParam('y_offset'),
    format: $format,
);

$image = $renderer->render($input);

header('Content-Type: ' . $format->mimeType());
header('Content-Length: ' . strlen($image));
header('Cache-Control: public, max-age=300');
echo $image;
