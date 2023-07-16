<?php
require_once("dictionary.php");

function buildStat($statJson){
    $object_playerstats = json_decode($statJson,true)["playerstats"];
    $object_stats = $object_playerstats["stats"];


    $stat_chara = [];
    $stat_card = [];
    $stat_bh = [];
    $stat_other = [];

    for($x=0;$x < sizeof($object_stats);$x++) {
        $thisStat = $object_stats[$x];
        $thisDisplayName = getDisplayName($thisStat["name"]);
        switch (getStatGroup($thisStat["name"])){
            case "chara_wins":
                $stat_chara[$thisDisplayName]["wins"] = $thisStat["value"];
                break;
            case "chara_games":
                $stat_chara[$thisDisplayName]["games"] = $thisStat["value"];
                break;
            case "card":
                $stat_card[$thisDisplayName] = $thisStat["value"];
                break;
            case "bh":
                $stat_bh[$thisDisplayName] = $thisStat["value"];
                break;
            default:
                $stat_other[$thisDisplayName]["value"] = $thisStat["value"];
                $stat_other[$thisDisplayName]["descr"] = getDescr($thisStat["name"]);
                break;
        }
    }

    return [$stat_chara,$stat_card,$stat_bh,$stat_other];
}