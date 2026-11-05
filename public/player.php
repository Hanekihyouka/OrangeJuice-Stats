<?php

declare(strict_types=1);

/**
 * Stats of a single player. Always live, never cached.
 *
 * Query: ?steamid=<SteamID64 or profile URL>
 */

use OJStats\Steam\PrivateStatsException;
use OJStats\Steam\SteamApiException;
use OJStats\Steam\SteamId;

/** @var \OJStats\App $app */
$app = require __DIR__ . '/../src/bootstrap.php';
$view = $app->view();

$input = trim((string) ($_GET['steamid'] ?? ''));
$steamId = SteamId::tryParse($input);
$errors = [];

if ($steamId !== null) {
    try {
        $stats = $app->players()->stats($steamId);
        $profile = $app->players()->profile($steamId);

        echo $view->render('layout', [
            'title' => "{$profile->name} - 百分百鲜橙汁个人统计",
            'description' => '玩家的等级、游戏场数、合作等级、角色胜场、胜率等统计信息。',
            'bodyClass' => $stats->level() >= 300 ? 'level-300' : '',
            'content' => $view->render('player', [
                'profile' => $profile,
                'stats' => $stats,
                'game' => $app->game(),
                'renderIds' => $app->renderers()->ids(),
            ]),
        ]);
        return;
    } catch (PrivateStatsException) {
        http_response_code(404);
        $errors = [
            '无法获取该玩家的统计数据，请将 个人资料 → 隐私设置 → 游戏详情 设置为公开。',
            "I can't access this user's stats. Verify their game details are set to public.",
        ];
    } catch (SteamApiException $e) {
        http_response_code(502);
        $errors = ['无法从 Steam 获取数据，请稍后再试。', $e->getMessage()];
    }
} elseif ($input !== '') {
    http_response_code(400);
    $errors = ['请输入 17 位的 Steam64 ID（7656119 开头），或者包含它的个人资料链接。'];
}

echo $view->render('layout', [
    'title' => '百分百鲜橙汁 - 个人统计',
    'description' => '玩家的等级、游戏场数、合作等级、角色胜场、胜率等统计信息。',
    'content' => $view->render('player-search', ['input' => $input, 'errors' => $errors]),
]);
