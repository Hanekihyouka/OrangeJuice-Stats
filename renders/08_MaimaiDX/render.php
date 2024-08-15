<?php
function build($contentObject,$profileObejct){
    $limit = 50;
    if(isset($_GET["limit"])){
        $limit = intval($_GET["limit"]);
        if($limit < 0){$limit = 0;}
        if($limit > sizeof($contentObject[0])){
            $limit = sizeof($contentObject[0]);
        }
    }
    $row_limit = ceil($limit/5);
    $im_w = 2048;
    //$im_h = 1626;
    $im_h = 346 + $row_limit*128;
//background
    $im = new Imagick("./renders/08_MaimaiDX/images/back.png");
    $im->cropImage($im_w,$im_h,0,0);
    $im->roundCornersImage(4, 4);
// Text
    $text = new Imagick();
    $text->newImage($im_w, $im_h, 'none');
    $text->setImageFormat('png');

    $draw = new ImagickDraw();
    // blank
    if ($row_limit>0){
        $draw->setFillColor("rgba(255, 255, 255, 0.55)");
        $draw->roundRectangle(57,289,$im_w-57,$im_h-45,30,30);
        $im->drawImage($draw);
        $draw->clear();
    }
    // info
    $draw->setFillColor(new ImagickPixel('#000'));
    $draw->setFont("./images/ARHei.ttf");
    // nick
    if(isset($_GET["nick"])){
        $nick = $_GET["nick"];
    }else{
        $nick = $profileObejct["personaname"];
    }
    $draw->setFontSize(72);
    $draw->annotation(600, 130, $nick);
    
    // Level && Online  Games  Win && xp++/level
    $draw->setFillColor(new ImagickPixel('#000'));
    $draw->setFontSize(54);
    $draw->annotation(603, 192, 'Lv.');
    $draw->setFontSize(48);
    $draw->annotation(1150, 106, 'Wins');//Online wins
    $draw->annotation(1150, 151, 'Played');//Online played
    $draw->annotation(1150, 196, 'Coop');//Coop played
    $draw->annotation(1150, 241, 'BH Wins');//Online BH wins
// chara slot
    //排序
    arsort($contentObject[0]);
    //遍历画数据
    $row=0;
    for(;$row<$row_limit;$row++){//10 行，每行 5 个，共 b50
        $row_y=300+$row*128;
        $i=0;
        for(;$i<5;$i++){
            $chara_index=$i+$row*5;
            if($chara_index==$limit){break 2;}
            if($chara_index<sizeof($contentObject[0])){
                $chara_key = array_keys($contentObject[0])[$chara_index];
                $chara_displayname = getDisplayName($chara_key);
                $chara_games = $contentObject[0][$chara_key]["games"];
                $chara_wins = $contentObject[0][$chara_key]["wins"];
                $slot_type=1;
                if($chara_games>99){$slot_type=2;}
                if($chara_games>499){$slot_type=3;}
                // draw slot
                $slot_blank = new Imagick("./renders/08_MaimaiDX/images/slot".$slot_type.".png");
                $im->compositeImage($slot_blank, $slot_blank->getImageCompose(), 64+$i*390,$row_y);
                // draw hyper, resize to 96x
                //$hyper_icon = getHyperIconName(key($contentObject[0]));
                $hyper_icon = getHyperIconName($chara_displayname);
                $im_hyper = new Imagick("./images/cards/hyper/".$hyper_icon."256.png");
                $im_hyper->resizeImage(96, 96, 0.9, 1, true);
                $im->compositeImage($im_hyper, $im_hyper->getImageCompose(), 64+$i*390+16,$row_y+12);

                // draw text, charaname  winrate  wins/games
                $draw->setFillColor(new ImagickPixel('#fff'));
                $draw->setFontSize(32);
                $draw->annotation($i*390+200,$row_y+38,$chara_displayname);
                if($chara_wins!=0){
                    $draw->annotation($i*390+230,$row_y+72,round(100*$chara_wins/$chara_games,2)."%");
                    $draw->setFillColor(new ImagickPixel('#000'));
                    $draw->setFontSize(22);
                    $draw->annotation($i*390+200,$row_y+100,$chara_wins." / ".$chara_games);
                    $chara_winrate = $chara_wins/$chara_games;
                    if($chara_games>19){
                        if($chara_winrate>.25){// 胜率小于 25%和场数不足 20 场的不评级
                            if($chara_winrate<.28){$tier = new Imagick("./renders/08_MaimaiDX/images/tierB.png");}
                            else if($chara_winrate<.30){$tier = new Imagick("./renders/08_MaimaiDX/images/tierBp.png");}
                            else if($chara_winrate<.35){$tier = new Imagick("./renders/08_MaimaiDX/images/tierA.png");}
                            else if($chara_winrate<.40){$tier = new Imagick("./renders/08_MaimaiDX/images/tierAp.png");}
                            else if($chara_winrate<.45){$tier = new Imagick("./renders/08_MaimaiDX/images/tierS.png");}
                            else if($chara_winrate<.50){$tier = new Imagick("./renders/08_MaimaiDX/images/tierSS.png");}
                            else if($chara_winrate<.80){$tier = new Imagick("./renders/08_MaimaiDX/images/tierSSS.png");}
                            else{$tier = new Imagick("./renders/08_MaimaiDX/images/tierSSSp.png");}
                            $im->compositeImage($tier, $tier->getImageCompose(), $i*390+340,$row_y+33);
                        }
                    }
                    if($chara_winrate>.97){//算你 fc 好了
                        $fullcombo = new Imagick("./renders/08_MaimaiDX/images/fullcombo.png");
                        $im->compositeImage($fullcombo, $fullcombo->getImageCompose(), $i*390+300,$row_y+80);
                    }
                }else{
                    $draw->annotation($i*390+230,$row_y+72," ~ ");
                    $draw->setFillColor(new ImagickPixel('#000'));
                    $draw->setFontSize(22);
                    $draw->annotation($i*390+200,$row_y+100," / ".$chara_games);
                }
            }
        }  
    }
      
//avatar
    $im_avatar = new Imagick($profileObejct["avatarfull"]);
    $im_avatar->roundCornersImage(25, 25);
// composite
    $im->compositeImage($im_avatar, $im_avatar->getImageCompose(), 400, 63);
    
    // Coop role levels
    $draw->setFontSize(40);
    $role_distance = 70;
    $role_x = 600;
    $role_y = 240;
    $draw -> setFillColor(new ImagickPixel('#E86D6D'));
    $draw -> annotation($role_x, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2A']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#5EB7DB'));
    $draw -> annotation($role_x + $role_distance*1, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2B']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#3CB371'));
    $draw -> annotation($role_x + $role_distance*2, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2C']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#F4A460'));
    $draw -> annotation($role_x + $role_distance*3, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2D']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#BA55D3'));
    $draw -> annotation($role_x + $role_distance*4, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2E']["value"]))/6));
    $draw->setFillColor(new ImagickPixel('#000'));//reset color

    $s_level = $contentObject[3]["STAT_PLAYER_LEVEL"]["value"];
    $draw->setStrokeWidth(1);
    $draw->setFontSize(42);
    $draw->annotation(663, 192, $s_level);
    
    $draw->setTextAlignment(3);
    //pvp合winrate
    $total_games = 0;
    $total_wins = 0;
    foreach ($contentObject[0] as $thisStat){
        $total_games += $thisStat["games"];
        $total_wins += $thisStat["wins"];
    }
    $draw->setFillColor(new ImagickPixel('#666'));
    $draw->setFontSize(32);
    $draw->annotation(1400, 102, "(".($total_games==0?0:round(100*$total_wins/$total_games,1))."%)");
    //bh合winrate
    $bh_games = $contentObject[3]["STAT_ONLINE_BH_GAMES_PLAYED"]["value"];
    $bh_wins = $contentObject[3]["STAT_ONLINE_BH_GAMES_WON"]["value"];
    $draw->annotation(1400, 237, "(".($bh_games==0?0:round(100*$bh_wins/$bh_games,1))."%)");
    $draw->setFillColor(new ImagickPixel('#000'));
    $draw->setFontSize(48);
    $draw->annotation(1500, 106, $contentObject[3]["STAT_ONLINE_GAMES_WON"]["value"]);
    $draw->annotation(1500, 151, $contentObject[3]["STAT_ONLINE_GAMES_PLAYED"]["value"]);
    $draw->annotation(1500, 196, $contentObject[3]["STAT_C_GAMES_PLAYED"]["value"]);
    $draw->annotation(1500, 241, $bh_wins);
// rende text
    $text->drawImage($draw);
    $im->compositeImage($text, $text->getImageCompose(), 0, 0);

// sp1
    $sp1 = 0;
    if(isset($_GET["sp1"])){
        $sp1 = intval($_GET["sp1"]);
    }
    if($sp1>0){
        $im_sp_file = "./images/sp1_".$sp1.".png";
        $im_sp = new Imagick($im_sp_file);
        $im_sp->resizeImage(64, 64, 0.9, 1, true);
        $im->compositeImage($im_sp, $im_sp->getImageCompose(), 553, 42);
    }
    

// render
    echo $im->getImageBlob();
}
