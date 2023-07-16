<?php
function build($contentObject,$profileObejct){
    $limit = 5;
    if(isset($_GET["limit"])){
        $limit = intval($_GET["limit"]);
        if($limit < 0){$limit = 0;}
        if($limit > sizeof($contentObject[0])){
            $limit = sizeof($contentObject[0]);
        }
    }

    //画板
    $im_w = 700;
    $im_h = 400+72*$limit+72;
    $im = imagecreatetruecolor($im_w,$im_h);
    //imagealphablending($im, false);
    imagesavealpha($im, true);
    $pngTransparency = imagecolorallocatealpha($im , 0, 0, 0, 127);
    imagefill($im,0,0,$pngTransparency);


    $font = "./renders/06_2023_AprilFools/images/方正静蕾.ttf";
    $im_base = imagecreatefrompng("./renders/06_2023_AprilFools/images/ap-render.png");
    //imagealphablending($im_base, false);
    //imagesavealpha($im_base , true);
    imagecopy($im, $im_base, 0, 0, 0, 0, imagesx($im_base), imagesy($im_base));

// avatar
    $angel = mt_rand(-20*1000,20*1000)/1000.0;
    $im_avatar = imagecreatefromjpeg($profileObejct["avatarfull"]);
    $im_avatar_ro = imagerotate($im_avatar,$angel,$pngTransparency);
    imagecopy($im, $im_avatar_ro, 80, 110, 0, 0, imagesx($im_avatar_ro), imagesy($im_avatar_ro));
    imagedestroy($im_avatar);
    imagedestroy($im_avatar_ro);
//sp1 for pin
    $sp1 = 0;
    if(isset($_GET["sp1"])){
        $sp1 = intval($_GET["sp1"]);
    }
//pin
    if($sp1 > 0){
        $im_sp1 = imagecreatefrompng("./images/sp1_".$sp1.".png");
        $im_sp1 = imagescale($im_sp1 , 54, 54);
        imagecopy($im,$im_sp1,190,60,0,0,imagesx($im_sp1),imagesy($im_sp1));
        imagedestroy($im_sp1);
    }
// nick
    if(isset($_GET["nick"])){
        $nick = $_GET["nick"];
    }else{
        $nick = $profileObejct["personaname"];
    }
    $color_orange = imagecolorallocate($im,254,207,142);
    $color_black = imagecolorallocate($im,0,0,0);
    $color_darkgray = imagecolorallocate($im,36,36,36);
    $color_gray = imagecolorallocate($im,127,127,127);
    $color_white = imagecolorallocate($im,255,255,255);

    imagefttext($im, 35+mt_rand(-8,8), mt_rand(-20*1000,20*1000)/1000.0, 230, 90, $color_black, $font, $nick);
// roles
    imagefttext($im, 30+mt_rand(-8,8), mt_rand(-20*1000,20*1000)/1000.0, 580, 145, $color_black, $font, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2A']["value"]))/6));
    imagefttext($im, 30+mt_rand(-8,8), mt_rand(-20*1000,20*1000)/1000.0, 590, 215, $color_black, $font, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2B']["value"]))/6));
    imagefttext($im, 30+mt_rand(-8,8), mt_rand(-20*1000,20*1000)/1000.0, 585, 290, $color_black, $font, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2C']["value"]))/6));
    imagefttext($im, 30+mt_rand(-8,8), mt_rand(-20*1000,20*1000)/1000.0, 580, 350, $color_black, $font, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2D']["value"]))/6));

    // level
    imagefttext($im, 35+mt_rand(-8,8), mt_rand(-20*1000,20*1000)/1000.0, 300, 200, $color_black, $font, "levels  ".$contentObject[3]["STAT_PLAYER_LEVEL"]["value"]);
    //胜场
    imagefttext($im,30+mt_rand(-8,8),mt_rand(-20*1000,20*1000)/1000.0,300,215,$color_black,$font,"wins  ".$contentObject[3]["STAT_ONLINE_GAMES_WON"]["value"]);
    //pvp合winrate
    $total_games = 0;
    $total_wins = 0;
    foreach ($contentObject[0] as $thisStat){
        $total_games += $thisStat["games"];
        $total_wins += $thisStat["wins"];
    }
    imagefttext($im,24+mt_rand(-8,8),mt_rand(-20*1000,20*1000)/1000.0,100,280,$color_darkgray,$font,"pvp:  "
        .$total_wins."/".$total_games."  =  "
        .round(100*($total_games==0?0:($total_wins/$total_games)),2)."%");
    //bh合winrate
    $bh_games = $contentObject[3]["STAT_ONLINE_BH_GAMES_PLAYED"]["value"];
    $bh_wins = $contentObject[3]["STAT_ONLINE_BH_GAMES_WON"]["value"];
    imagefttext($im,24+mt_rand(-8,8),mt_rand(-20*1000,20*1000)/1000.0,100,340,$color_darkgray,$font,"bh:  "
        .$bh_wins."/".$bh_games."  =  "
        .round(100*($bh_games==0?0:($bh_wins/$bh_games)),2)."%");
    //场数、coop场数
    imagefttext($im,24+mt_rand(-8,8),mt_rand(-20*1000,20*1000)/1000.0,300,320-45,$color_darkgray,$font,
        "online games:  ".$contentObject[3]["STAT_ONLINE_GAMES_PLAYED"]["value"]);
    imagefttext($im,24+mt_rand(-8,8),mt_rand(-20*1000,20*1000)/1000.0,300,350-40,$color_darkgray,$font,
        "coop games:  ".$contentObject[3]["STAT_C_GAMES_PLAYED"]["value"]);


    /**
    todo

     **/

    //表头
    if($limit > 0){
        imagefttext($im,24+mt_rand(-8,8),mt_rand(-20*1000,20*1000)/1000.0,170,390,$color_gray,$font,"games");
        imagefttext($im,24+mt_rand(-8,8),mt_rand(-20*1000,20*1000)/1000.0,170+120,390,$color_gray,$font,"wins");
        imagefttext($im,24+mt_rand(-8,8),mt_rand(-20*1000,20*1000)/1000.0,170+220,390,$color_gray,$font,"winrate %");
    }
    //排序
    arsort($contentObject[0]);
    //按行画数据
    $i=0;
    foreach ($contentObject[0] as $key => $value){
        if ($i<$limit){
            //INNER
            $im_INNER = imagecreatefrompng("./renders/06_2023_AprilFools/images/INNER".mt_rand(1,5).".png");
            //imagealphablending($im_INNER, false);
            //imagesavealpha($im_INNER , true);
            imagecopy($im,$im_INNER,0,400+$i*72,0,0,imagesx($im_INNER),imagesy($im_INNER));
            imagedestroy($im_INNER);

            $im_icon_chara = imagecreatefrompng("./images/chara/".$key.".png");
            imagecopy($im,$im_icon_chara,53,400+$i*72,0,0,imagesx($im_icon_chara),imagesy($im_icon_chara));
            imagedestroy($im_icon_chara);
            imagefttext($im,26+mt_rand(-8,8),mt_rand(-20*1000,20*1000)/1000.0,170,505+$i*72-50,$color_black,$font,$value["games"]);
            imagefttext($im,26+mt_rand(-8,8),mt_rand(-20*1000,20*1000)/1000.0,170+120,505+$i*72-50,$color_black,$font,$value["wins"]);
            if (isset($value["wins"])&&($value["wins"]!=0)){
                imagefttext($im,26+mt_rand(-8,8),mt_rand(-20*1000,20*1000)/1000.0,170+220,505+$i*72-50,$color_black,$font,round(100*$value["wins"]/$value["games"],2));
            }else{
                imagefttext($im,26+mt_rand(-8,8),mt_rand(-20*1000,20*1000)/1000.0,170+220,505+$i*72-50,$color_black,$font,0);
            }
            imagefttext($im,26+mt_rand(-8,8),mt_rand(-20*1000,20*1000)/1000.0,170+330,505+$i*72-50,$color_black,$font,$key);
        }else{
            break;
        }
        $i++;
    }
    //ENDER
    $im_END = imagecreatefrompng("./renders/06_2023_AprilFools/images/END.png");
    //imagesavealpha($im_END , true);
    imagecopy($im,$im_END,0,400+$i*72,0,0,imagesx($im_END),imagesy($im_END));
    imagedestroy($im_END);


    imagepng($im);
    imagedestroy($im);
}
