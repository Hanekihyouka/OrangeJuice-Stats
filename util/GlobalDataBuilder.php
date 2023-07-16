<?php
require_once ("dictionary.php");

function buildStat($statJson,$isDateSet = false){
    $statJsonGames = $statJson["games"];
    $statJsonWins = $statJson["wins"];

    $object_games = json_decode($statJsonGames,true)["response"]["globalstats"];
    $object_wins = json_decode($statJsonWins,true)["response"]["globalstats"];
    $object_stats = [];
    foreach ( $object_games as $key => $value ) {
        $object_stats[getDisplayName($key)]["games"] = $isDateSet?historySum($value["history"]):$value["total"];
    }
    foreach ( $object_wins as $key => $value ) {
        $object_stats[getDisplayName($key)]["wins"] = $isDateSet?historySum($value["history"]):$value["total"];
    }
    return $object_stats;
}

function historySum($historyStats){
    $count = 0;
    foreach ( $historyStats as $key => $dayStat){
        $count += $dayStat["total"];
    }
    return $count;
}
