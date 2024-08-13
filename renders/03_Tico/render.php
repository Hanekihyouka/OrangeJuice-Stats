<?php
function build($contentObject,$profileObejct){
    $font = "./images/ARHei.ttf";
    $im = imagecreatefrompng("./renders/03_Tico/images/Tico.png");
// avatar
    $im_avatar = imagecreatefromjpeg($profileObejct["avatarfull"]);
    imagecopy($im, $im_avatar, 60, 90, 0, 0, imagesx($im_avatar), imagesy($im_avatar));
    imagedestroy($im_avatar);
//sp1 for pin
    $sp1 = 0;
    if(isset($_GET["sp1"])){
        $sp1 = intval($_GET["sp1"]);
    }
//pin
    if($sp1 > 0){
        $im_sp1 = imagecreatefrompng("./images/sp1_".$sp1.".png");
        $im_sp1 = imagescale($im_sp1 , 54, 54);
        imagecopy($im,$im_sp1,200,60,0,0,imagesx($im_sp1),imagesy($im_sp1));
        imagedestroy($im_sp1);
    }
// nick
    if(isset($_GET["nick"])){
        $nick = $_GET["nick"];
    }else{
        $nick = $profileObejct["personaname"];
    }
    $color_black = imagecolorallocate($im, 0, 0, 0);
    imagefttext($im, 30, 0, 450, 40, $color_black, $font, $nick);
// level
    imagefttext($im, 20, 0, 670, 30, $color_black, $font, $contentObject[3]["STAT_PLAYER_LEVEL"]["value"]);
// roles
    imagefttext($im, 20, 0, 580, 100, $color_black, $font, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2A']["value"]))/6));
    imagefttext($im, 20, 0, 610, 140, $color_black, $font, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2B']["value"]))/6));
    imagefttext($im, 20, 0, 610, 179, $color_black, $font, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2C']["value"]))/6));
    imagefttext($im, 20, 0, 610, 210, $color_black, $font, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2D']["value"]))/6));
    imagefttext($im, 20, 0, 610, 253, $color_black, $font, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2E']["value"]))/6));
// pvp wins wr , bh wins
    imagefttext($im, 20, 0, 530, 300, $color_black, $font, $contentObject[3]["STAT_ONLINE_GAMES_WON"]["value"]);
    //pvp合winrate
    $total_games = 0;
    $total_wins = 0;
    foreach ($contentObject[0] as $thisStat){
        $total_games += $thisStat["games"];
        $total_wins += $thisStat["wins"];
    }
    imagefttext($im, 20, 0, 540, 330, $color_black, $font, round(100*($total_games==0?0:($total_wins/$total_games)),2)."%");
    //bh
    $bh_games = $contentObject[3]["STAT_ONLINE_BH_GAMES_PLAYED"]["value"];
    $bh_wins = $contentObject[3]["STAT_ONLINE_BH_GAMES_WON"]["value"];
    imagefttext($im, 20, 0, 515, 360, $color_black, $font, $bh_wins."/".$bh_games."  =  "
        .round(100*($bh_games==0?0:($bh_wins/$bh_games)),2)."%");


    imagepng($im);
    imagedestroy($im);
}
