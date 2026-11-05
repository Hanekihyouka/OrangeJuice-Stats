<?php

declare(strict_types=1);

/**
 * Global character stats, optionally limited to a range of UTC days.
 *
 * Query: ?start=Y-m-d&end=Y-m-d   (legacy: ?startdate=<unix>&enddate=<unix>)
 */

use OJStats\Stats\DateRange;
use OJStats\Steam\SteamApiException;

/** @var \OJStats\App $app */
$app = require __DIR__ . '/../src/bootstrap.php';

$range = DateRange::tryParse($_GET['start'] ?? null, $_GET['end'] ?? null);
if ($range === null && ctype_digit((string) ($_GET['startdate'] ?? '')) && ctype_digit((string) ($_GET['enddate'] ?? ''))) {
    $range = DateRange::of((int) $_GET['startdate'], (int) $_GET['enddate']);
}

$stats = null;
$error = null;
try {
    set_time_limit(180); // the very first query of a long range fills the cache
    $stats = $range === null ? $app->globalStats()->allTime() : $app->globalStats()->forRange($range);
} catch (SteamApiException $e) {
    http_response_code(502);
    $error = '无法从 Steam 获取数据，请稍后再试。（' . $e->getMessage() . '）';
}

$view = $app->view();
echo $view->render('layout', [
    'title' => '百分百鲜橙汁 - 全球统计',
    'description' => '可选择日期的，角色的全球总场数、胜场、胜率等统计信息。',
    'styles' => ['css/daterangepicker.css'],
    'scripts' => ['js/moment.min.js', 'js/daterangepicker.min.js'],
    'content' => $view->render('global', [
        'stats' => $stats,
        'error' => $error,
        'earliestDate' => $app->config->earliestDate,
        'today' => DateRange::format(DateRange::today()),
    ]),
]);
