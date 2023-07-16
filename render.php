<?php
//ini_set("display_errors", "On");//打开错误提示
//ini_set("error_reporting",E_ALL);//显示所有错误
require_once ("util/StatJsonParser.php");
require_once ("util/PlayerDataBuilder.php");
header("Content-type: image/png");


$contentObject = [];
$profileObejct = [];
if (isset($_GET["steamid"])){
    $contentRequest = requestJsonPlayer($_GET["steamid"]);
    $profileRequest = requestJsonProfile($_GET["steamid"]);
    if ($contentRequest&&$profileRequest){
        $contentObject = buildStat($contentRequest);
        $profileObejct = json_decode($profileRequest,true)["response"]["players"][0];
    }else{
        die(0);
    }
}

$render = 0;
$renders = ["default","01_Mori","02_Mori","03_Tico","04_TentekiKo","05_TentekiKo","06_2023_AprilFools"];
if (isset($_GET["render"])){
    $render=$_GET["render"];
}

require_once ("renders/".$renders[$render]."/render.php");
build($contentObject,$profileObejct);


