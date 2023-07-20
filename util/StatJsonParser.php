<?php
require_once ("dictionary.php");

function requestJsonPlayer($steam64id){
    return file_get_contents(getUrlHeaderPlayer().getSteamWebKey()."&steamid=".$steam64id)?:false;
}

function requestJsonGlobal($startdate = false,$enddate = false){
    $games = getDictionaryGames();
    $wins = getDictionaryWins();
    $url_games = getUrlHeaderGlobal()."&count=".sizeof($games);
    $url_wins = getUrlHeaderGlobal()."&count=".sizeof($wins);
    for ($i = 0 ; $i < sizeof($games) ; $i++){$url_games .= "&name[".$i."]=".$games[$i];}
    for ($i = 0 ; $i < sizeof($wins) ; $i++){$url_wins .= "&name[".$i."]=".$wins[$i];}
    if ($startdate&&$enddate){
        $url_games .= "&startdate=".$startdate."&enddate=".$enddate;
        $url_wins .= "&startdate=".$startdate."&enddate=".$enddate;
    }
    return ["games" => file_get_contents($url_games),
        "wins" => file_get_contents($url_wins)];
}

function requestJsonGlobal41($statKey){
    $responseContent = file_get_contents(getUrlHeaderGlobal()."&count=1"."&name[0]=".$statKey)?:false;
    if ($responseContent){
        $responseObject = json_decode($responseContent,true)["response"];
        switch ($responseObject["result"]){
            case 1:
                return $responseObject["globalstats"][$statKey]["total"];
            case 8:
                return $responseObject["error"];
            default:
                return "未知错误_".$responseObject["result"];
        }
    }else{
        return "请求失败";
    }
}

function requestJsonProfile($steam64id){
    return file_get_contents(getUrlHeaderProfile().getSteamWebKey()."&steamids=".$steam64id)?:false;
}

function requestJsonSchema(){
    return file_get_contents(getUrlHeaderSchema().getSteamWebKey()."&l=schinese")?:false;
}

function getSteamWebKey(){
	return ""; // Your Steam WebAPI-Key found at https://steamcommunity.com/dev/apikey
}
function getUrlHeaderPlayer(){
    return "http://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v0002/?appid=282800&key=";
}
function getUrlHeaderProfile(){
    return "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=";
}
function getUrlHeaderGlobal(){
    return "https://api.steampowered.com/ISteamUserStats/GetGlobalStatsForGame/v1/?appid=282800";
}
function getUrlHeaderSchema(){
    return "http://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/?appid=282800&key=";
}
