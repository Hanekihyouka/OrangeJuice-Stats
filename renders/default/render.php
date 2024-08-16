<?php
function build($contentObject,$profileObejct){
    $limit = 5;
    if(isset($_GET["limit"])){
        $limit = intval($_GET["limit"]);
        if($limit < 0){$limit = 0;}
    }
    if($limit > sizeof($contentObject[0])){
        $limit = sizeof($contentObject[0]);
    }
    //sp1 for pin
    $sp1 = 0;
    if(isset($_GET["sp1"])){
        $sp1 = intval($_GET["sp1"]);
    }
    //画板
    $im_w = 700;
    $im_h = 50+400+72*$limit+50;
    $im = imagecreatetruecolor($im_w,$im_h);
    imagesavealpha($im, true);
    //背景 782*391 重复
    if ($contentObject[3]["STAT_PLAYER_LEVEL"]["value"]>=300){
        $im_background = imagecreatefrompng("./images/background2.png");
    }else{
        $im_background = imagecreatefrompng("./images/background.png");
    }
    for ($i = 0 ; $i < $im_h/imagesy($im_background) ; $i++){
        imagecopy($im,$im_background,-41,$i*imagesy($im_background),0,0,imagesx($im_background),imagesy($im_background));
    }
    $color_orange = imagecolorallocate($im,254,207,142);
    $color_black = imagecolorallocate($im,0,0,0);
    $color_darkgray = imagecolorallocate($im,36,36,36);
    $color_gray = imagecolorallocate($im,127,127,127);
    $color_white = imagecolorallocate($im,255,255,255);
    imagefilledrectangle($im,50,50,$im_w-50,$im_h-50,$color_gray);//灰色外框
    imagefilledrectangle($im,55,55,$im_w-55,$im_h-55,$color_orange);//橙色内部背景
    imagefilledrectangle($im,97,97+25,286,286+25,$color_gray);//灰色外框_头像
    //头像
    $im_avatar = imagecreatefromjpeg($profileObejct["avatarfull"]);
    imagecopy($im,$im_avatar,100,100+25,0,0,imagesx($im_avatar),imagesy($im_avatar));
    imagedestroy($im_avatar);
    //合作职业icon
    for ($i = 0 ; $i < 5 ;$i++){
        $im_role = imagecreatefrompng("./images/role".$i.".png");
        imagecopy($im,$im_role,500,100+$i*70,0,0,imagesx($im_role),imagesy($im_role));
        imagedestroy($im_role);
    }
    //pin
    if($sp1 > 0){
        $im_sp1 = imagecreatefrompng("./images/sp1_".$sp1.".png");
        $im_sp1 = imagescale($im_sp1 , 96, 96);
        imagecopy($im,$im_sp1,530,0,0,0,imagesx($im_sp1),imagesy($im_sp1));
        imagedestroy($im_sp1);
    }
    //表奇数行，填白色
    for ($i = 0 ; $i < $limit ;$i++){
        if ($i%2==0){
            if ($i==$limit-1){
                imagefilledrectangle($im,55,450+72*$i,$im_w-55,450+72*$i+72-5,$color_white);
            }else{
                imagefilledrectangle($im,55,450+72*$i,$im_w-55,450+72*$i+72,$color_white);
            }
        }
    }
    //名字
    if(isset($_GET["nick"])){
        $nick = $_GET["nick"];
    }else{
        $nick = $profileObejct["personaname"];
    }
    $nick_wd = imagettfbbox(30,0,"./images/SourceHanSansCN-Heavy-2.otf",$nick)[2];
    imagefttext($im,30,0,(700-$nick_wd)/2,150-40,$color_black,"./images/SourceHanSansCN-Heavy-2.otf",$nick);

    //等级
    imagefttext($im,27,0,300,215-50,$color_black,"./images/SAO.otf","lvl  ".$contentObject[3]["STAT_PLAYER_LEVEL"]["value"]);
    //胜场
    imagefttext($im,27,0,300,275-50,$color_black,"./images/SAO.otf","wins  ".$contentObject[3]["STAT_ONLINE_GAMES_WON"]["value"]);
    //pvp合winrate
    $total_games = 0;
    $total_wins = 0;
    foreach ($contentObject[0] as $thisStat){
        $total_games += $thisStat["games"];
        $total_wins += $thisStat["wins"];
    }
    imagefttext($im,24,0,100,320+40,$color_darkgray,"./images/SAO.otf","pvp:  "
        .$total_wins."/".$total_games."  =  "
        .round(100*($total_games==0?0:($total_wins/$total_games)),2)."%");
    //bh合winrate
    $bh_games = $contentObject[3]["STAT_ONLINE_BH_GAMES_PLAYED"]["value"];
    $bh_wins = $contentObject[3]["STAT_ONLINE_BH_GAMES_WON"]["value"];
    imagefttext($im,24,0,100,360+40,$color_darkgray,"./images/SAO.otf","bh:  "
        .$bh_wins."/".$bh_games."  =  "
        .round(100*($bh_games==0?0:($bh_wins/$bh_games)),2)."%");
    //场数、coop场数
    imagefttext($im,24,0,300,320-45,$color_darkgray,"./images/SAO.otf",
        "online games:  ".$contentObject[3]["STAT_ONLINE_GAMES_PLAYED"]["value"]);
    imagefttext($im,24,0,300,350-40,$color_darkgray,"./images/SAO.otf",
        "coop games:  ".$contentObject[3]["STAT_C_GAMES_PLAYED"]["value"]);

    //合作职业等级
    imagefttext($im,27,0,570,155,$color_darkgray,"./images/SAO.otf",floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2A']["value"]))/6));
    imagefttext($im,27,0,570,155+70,$color_darkgray,"./images/SAO.otf",floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2B']["value"]))/6));
    imagefttext($im,27,0,570,155+70*2,$color_darkgray,"./images/SAO.otf",floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2C']["value"]))/6));
    imagefttext($im,27,0,570,155+70*3,$color_darkgray,"./images/SAO.otf",floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2D']["value"]))/6));
    imagefttext($im,27,0,570,155+70*4,$color_darkgray,"./images/SAO.otf",floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2E']["value"]))/6));
    //表头
    if($limit > 0){
        imagefttext($im,24,0,170,445,$color_gray,"./images/SAO.otf","games");
        imagefttext($im,24,0,170+120,445,$color_gray,"./images/SAO.otf","wins");
        imagefttext($im,24,0,170+220,445,$color_gray,"./images/SAO.otf","winrate %");
    }

    //排序
    arsort($contentObject[0]);
    //按行画数据
    $i=0;
    foreach ($contentObject[0] as $key => $value){
        if ($i<$limit){
            $im_icon_chara = imagecreatefrompng("./images/chara/".$key.".png");
            imagecopy($im,$im_icon_chara,53,450+$i*72,0,0,imagesx($im_icon_chara),imagesy($im_icon_chara));
            imagedestroy($im_icon_chara);
            imagefttext($im,26,0,170,505+$i*72,$color_black,"./images/SAO.otf",$value["games"]);
            imagefttext($im,26,0,170+120,505+$i*72,$color_black,"./images/SAO.otf",$value["wins"]);
            if (isset($value["wins"])&&($value["wins"]!=0)){
                imagefttext($im,26,0,170+220,505+$i*72,$color_black,"./images/SAO.otf",round(100*$value["wins"]/$value["games"],2));
            }else{
                imagefttext($im,26,0,170+220,505+$i*72,$color_black,"./images/SAO.otf",0);
            }
            imagefttext($im,26,0,170+330,505+$i*72,$color_black,"./images/SAO.otf",$key);
        }else{
            break;
        }
        $i++;
    }

    imagepng($im);

    imagedestroy($im);
    imagedestroy($im_background);
}
