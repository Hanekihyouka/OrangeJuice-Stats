<?php
function build($contentObject,$profileObejct){
    $im_w = 500;
    $im_h = 576;

//background
    $im = new Imagick("./renders/07_AstralParty/images/back.png");
    $im->roundCornersImage(4, 4);
// pin
// sp1
    $sp1 = 0;
    if(isset($_GET["sp1"])){
        $sp1 = intval($_GET["sp1"]);
    }
    if($sp1>0){
        $im_sp_file = "./images/sp1_".$sp1.".png";
        $im_sp = new Imagick($im_sp_file);
        $im_sp->resizeImage(35, 35, 0.9, 1, true);
        $im->compositeImage($im_sp, $im_sp->getImageCompose(), 360, 55);
    }

// Text
    $text = new Imagick();
    $text->newImage($im_w, $im_h, 'none');
    $text->setImageFormat('png');

    $draw = new ImagickDraw();
    
    // info
    $draw->setFillColor(new ImagickPixel('#000'));
    $draw -> setFont("./renders/07_AstralParty/images/Clash_Regular_Final.ttf");
    // nick
    if(isset($_GET["nick"])){
        $nick = $_GET["nick"];
    }else{
        $nick = $profileObejct["personaname"];
    }
    $draw->setFontSize(17);
    //描边
    $draw->setFillColor(new ImagickPixel('#000'));
    $draw->setStrokeColor(new ImagickPixel('rgba(110, 110, 110, 0.5)'));
    $draw->setStrokeWidth(1);
    $draw->annotation(131, 394, $nick);
    // 重置描边属性
    $draw->setStrokeWidth(0);
    $draw->setStrokeColor('transparent');
    
    // date and uuid
    $draw->setFillColor(new ImagickPixel('#666'));
    $draw->setFontSize(12);
    $draw->annotation(35, 423, date("Y-m-d"));
    $draw->annotation(185, 423, $_GET["steamid"]);
    // Level && Online  Games  Win && xp++/level
    $draw->setFont("./renders/07_AstralParty/images/Clash_Regular_Final.ttf");
    $draw->setFillColor(new ImagickPixel('#000'));
    $draw->setFontSize(12);
    $draw->annotation(131, 370, 'Lv.');
    $draw->setFontSize(19);
    $draw->annotation(143, 113, 'Wins');//Online wins
    $draw->annotation(143, 156, 'Played');//Online played
    $draw->annotation(143, 199, 'Coop');//Coop played
    $draw->annotation(143, 242, 'BH Wins');//Online BH wins
// chara slot
    //排序
    arsort($contentObject[0]);
    //遍历画数据
    $i=0;
    for(;$i<5;$i++){
        $chara_key = array_keys($contentObject[0])[$i];
        if(file_exists("./images/chara/".$chara_key.".png")){
            $im_icon_chara = new Imagick("./images/chara/".array_keys($contentObject[0])[$i].".png");
            $im->compositeImage($im_icon_chara, $im_icon_chara->getImageCompose(), 29+$i*93,447);
            $chara_games = $contentObject[0][$chara_key]["games"];
            $chara_wins = $contentObject[0][$chara_key]["wins"];
            $draw->setTextAlignment(3);
            $draw->setFontSize(12);
            $draw->annotation(105+$i*93,525,$chara_games);
            if($chara_wins!=0){
                $draw->setFontSize(19);
                $draw->annotation(100+$i*93,548,round(100*$chara_wins/$chara_games,2)."%");
                $draw->setFontSize(12);
                $draw->setTextAlignment(1);
                $draw->annotation(25+$i*93,455,$chara_wins);
            }
            $draw->setTextAlignment(1);
            if($i==0){
                // draw hyper
                $hyper_icon = getHyperIconName(key($contentObject[0]));
                if(isset($_GET["hyper_icon"])){
                    $hyper_icon = $_GET["hyper_icon"];
                }
                $im_hyper = new Imagick("./images/cards/cards/".$hyper_icon."256.png");
                # cut
                //$im_hyper -> cropImage(237,76,19,30);
                $render7_offset_y = getHyperIconAnchorY(key($contentObject[0]));
                if(isset($_GET["y_offset"])){
                        $render7_offset_y = $_GET["y_offset"];
                }
                if($render7_offset_y<0){
                    $render7_offset_y=-$render7_offset_y;
                    $im_hyper -> flopImage();
                }
                $im_hyper -> cropImage(237,76,19,$render7_offset_y);
                $im_hyper->roundCorners(18, 18);
                $im->compositeImage($im_hyper, $im_hyper->getImageCompose(), 111,329);
                //draw most uesed chara again
                    $im_pin_name = getFaceName(key($contentObject[0]));
                    $im_pin_index = "_00_03.png";
                    $im_pin_file = "./renders/05_TentekiKo/images/pin/".$im_pin_name.$im_pin_index;
                    //for safe
                    if(!file_exists($im_pin_file)){
                        $im_pin_index = "_00_00.png";
                        $im_pin_file = "./renders/05_TentekiKo/images/pin/".$im_pin_name.$im_pin_index;
                    }
                    if(!file_exists($im_pin_file)){
                        $im_pin_index = "_00_01.png";
                        $im_pin_file = "./renders/05_TentekiKo/images/pin/".$im_pin_name.$im_pin_index;
                    }
                    //for extend
                    if(!file_exists($im_pin_file)){
                        $im_pin_index = "_01_00_03.png";
                        $im_pin_file = "./renders/05_TentekiKo/images/pin/".$im_pin_name.$im_pin_index;
                    }
                    if(!file_exists($im_pin_file)){
                        $im_pin_index = "_04_00_03.png";
                        $im_pin_file = "./renders/05_TentekiKo/images/pin/".$im_pin_name.$im_pin_index;
                    }
                    if(!file_exists($im_pin_file)){
                        $im_pin_index = "_10_00_03.png";
                        $im_pin_file = "./renders/05_TentekiKo/images/pin/".$im_pin_name.$im_pin_index;
                    }
                    if(!file_exists($im_pin_file)){
                        $im_pin_index = "_11_00_03.png";
                        $im_pin_file = "./renders/05_TentekiKo/images/pin/".$im_pin_name.$im_pin_index;
                    }
                    if(!file_exists($im_pin_file)){
                        $im_pin_index = "_12_00_03.png";
                        $im_pin_file = "./renders/05_TentekiKo/images/pin/".$im_pin_name.$im_pin_index;
                    }
                    if(!file_exists($im_pin_file)){
                        $im_pin_index = "_00_00_03.png";
                        $im_pin_file = "./renders/05_TentekiKo/images/pin/".$im_pin_name.$im_pin_index;
                    }
                    if(!file_exists($im_pin_file)){
                        $im_pin_file = "./renders/04_TentekiKo/images/pin/"."silhouette_00.png";
                    }
                    $im_pin = new Imagick($im_pin_file);
                    $im_pin->resizeImage(128, 128, 0.9, 1, true);
                    $im->compositeImage($im_pin, $im_pin->getImageCompose(), 332, 310);
            }
        }else{
            $im_icon_chara = new Imagick("./renders/07_AstralParty/images/empty_slot.png");
            $im->compositeImage($im_icon_chara, $im_icon_chara->getImageCompose(), 26+$i*93,473);
        }
    }    
//avatar
    $im_avatar = new Imagick($profileObejct["avatarfull"]);
    $im_avatar->roundCornersImage(50, 50);
    $im_avatar->resizeImage(76, 76, 0.9, 1, true);
// composite
    $im->compositeImage($im_avatar, $im_avatar->getImageCompose(), 53, 329);
    
    // Coop role levels
    $draw->setFontSize(19);
    $role_distance = 50;
    $role_x = 147;
    $role_y = 285;
    $draw -> setFillColor(new ImagickPixel('#F08080'));
    $draw -> annotation($role_x, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2A']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#87CEEB'));
    $draw -> annotation($role_x + $role_distance*1, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2B']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#3CB371'));//#98FB98 is too bright
    $draw -> annotation($role_x + $role_distance*2, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2C']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#F4A460'));
    $draw -> annotation($role_x + $role_distance*3, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2D']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#BA55D3'));
    $draw -> annotation($role_x + $role_distance*4, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2E']["value"]))/6));
    $draw->setFillColor(new ImagickPixel('#000'));//reset color

    $s_level = $contentObject[3]["STAT_PLAYER_LEVEL"]["value"];
    //$s_lxp = $contentObject[3]["STAT_PLAYER_EXP"]["value"];
    //$p_rate = 1 - ((1300 + 100*$s_level)*$s_level/2 - $s_lxp) / (600+100*$s_level);
    $draw->setFontSize(24);
    //描边
    // not help much but maybe batter than nothing?
    $draw->setFillColor(new ImagickPixel('#000'));
    $draw->setStrokeColor(new ImagickPixel('rgba(110, 110, 110, 0.8)'));
    $draw->setStrokeWidth(1);
    $draw->annotation(150, 370, $s_level);
    // 重置描边属性
    $draw->setStrokeWidth(0);
    $draw->setStrokeColor('transparent');
    // Alignment  -->> right align
    // !!  important   !!
    // Alignment  -->> right align
    $draw->setTextAlignment(3);
    //pvp合winrate
    $total_games = 0;
    $total_wins = 0;
    foreach ($contentObject[0] as $thisStat){
        $total_games += $thisStat["games"];
        $total_wins += $thisStat["wins"];
    }
    $draw->setFillColor(new ImagickPixel('#666'));
    $draw->setFontSize(12);
    $draw->annotation(260, 113, "(".($total_games==0?0:round(100*$total_wins/$total_games,1))."%)");
    //bh合winrate
    $bh_games = $contentObject[3]["STAT_ONLINE_BH_GAMES_PLAYED"]["value"];
    $bh_wins = $contentObject[3]["STAT_ONLINE_BH_GAMES_WON"]["value"];
    $draw->annotation(290, 242, "(".($bh_games==0?0:round(100*$bh_wins/$bh_games,1))."%)");
    $draw->setFillColor(new ImagickPixel('#000'));
    $draw->setFontSize(19);
    $draw->annotation(360, 113, $contentObject[3]["STAT_ONLINE_GAMES_WON"]["value"]);
    $draw->annotation(360, 156, $contentObject[3]["STAT_ONLINE_GAMES_PLAYED"]["value"]);
    $draw->annotation(360, 199, $contentObject[3]["STAT_C_GAMES_PLAYED"]["value"]);
    $draw->annotation(360, 242, $bh_wins);
// rende text
    $text->drawImage($draw);
    $im->compositeImage($text, $text->getImageCompose(), 0, 0);


// render
    echo $im->getImageBlob();
}

