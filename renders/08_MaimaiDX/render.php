<?php
function build($contentObject,$profileObejct){
    $limit = 50;
    if(isset($_GET["limit"])){
        $limit = intval($_GET["limit"]);
        if($limit < 0){$limit = 0;}
    }
    if($limit > sizeof($contentObject[0])){
        $limit = sizeof($contentObject[0]);
    }
    $row_limit = ceil($limit/5);
    $im_w = 1800;
    //$im_h = 1626;
    $im_h = 250 + $row_limit*118;
//background
    $im = new Imagick("./renders/08_MaimaiDX/images/back.png");
    $im->cropImage($im_w,$im_h,0,0);
    $im->roundCornersImage(4, 4);
// Text
    $text = new Imagick();
    $text->newImage($im_w, $im_h, 'none');
    $text->setImageFormat('png');

    $draw = new ImagickDraw();    
    // profile
    $profile_x = 612;
    $profile_y = 76;

    // blank
    if ($row_limit>0){
        $draw->setFillColor("rgba(255, 255, 255, 0.55)");
        $draw->roundRectangle($profile_x-210,$profile_y-57,$profile_x+790,$profile_y+147,30,30);
        $draw->roundRectangle(13,$profile_y+167,$im_w-13,$im_h-9,15,15);
        $im->drawImage($draw);
        $draw->clear();
    }
    
    $draw->setFillColor(new ImagickPixel('#000'));
    $draw->setFont("./images/ARHei.ttf");
    // nick
    if(isset($_GET["nick"])){
        $nick = $_GET["nick"];
    }else{
        $nick = $profileObejct["personaname"];
    }
    $draw->setFontSize(72);
    $draw->annotation($profile_x, $profile_y+8, $nick);
    
    // Level && Online  Games  Win && xp++/level
    $draw->setFillColor(new ImagickPixel('#000'));
    $draw->setFontSize(54);
    $draw->annotation($profile_x+3, $profile_y+92, 'Lv.');
    $draw->setFontSize(48);
    $draw->annotation($profile_x+415, $profile_y+6, 'Wins');//Online wins
    $draw->annotation($profile_x+415, $profile_y+51, 'Played');//Online played
    $draw->annotation($profile_x+415, $profile_y+96, 'Coop');//Coop played
    $draw->annotation($profile_x+415, $profile_y+141, 'BH Wins');//Online BH wins
// chara slot
    //排序
    arsort($contentObject[0]);
    //遍历画数据
    $row=0;
    for(;$row<$row_limit;$row++){//10 行，每行 5 个，共 b50
        $row_y=241+$row*118;
        $i=0;
        for(;$i<5;$i++){
            $chara_index=$i+$row*5;
            if($chara_index==$limit){break 2;}
            if($chara_index<sizeof($contentObject[0])){
                $instance_x = $i*353-50;
                $chara_key = array_keys($contentObject[0])[$chara_index];
                $chara_displayname = getDisplayName($chara_key);
                $chara_games = $contentObject[0][$chara_key]["games"];
                $chara_wins = $contentObject[0][$chara_key]["wins"];
                $slot_type=1;
                // what im fk doing
                $slot_type = strlen($chara_displayname)%4;
                // draw slot
                $slot_blank = new Imagick("./renders/08_MaimaiDX/images/slot".$slot_type.".png");
                $im->compositeImage($slot_blank, $slot_blank->getImageCompose(), 64+$instance_x,$row_y);
                // draw hyper, resize to 96x
                //$hyper_icon = getHyperIconName(key($contentObject[0]));
                $hyper_icon = getHyperIconName($chara_displayname);
                $im_hyper = new Imagick("./images/cards/hyper/".$hyper_icon."256.png");
                $im_hyper->resizeImage(96, 96, 0.9, 1, true);
                $im->compositeImage($im_hyper, $im_hyper->getImageCompose(), 64+$instance_x+16,$row_y+12);

                // draw text, charaname  winrate  wins/games
                $draw->setFillColor(new ImagickPixel('#fff'));
                $draw->setFontSize(32);
                $draw->setFont("./images/SAO.otf");
                $draw->annotation($instance_x+200,$row_y+38,$chara_displayname);
                if($chara_wins!=0){
                    $draw->annotation($instance_x+230,$row_y+72,round(100*$chara_wins/$chara_games,2)."%");
                    $draw->setFillColor(new ImagickPixel('#000'));
                    $draw->setFontSize(22);
                    $draw->annotation($instance_x+200,$row_y+100,$chara_wins." / ".$chara_games);
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
                            $im->compositeImage($tier, $tier->getImageCompose(), $instance_x+327,$row_y+12);
                        }
                    }
                    if($chara_winrate>.97){//算你 fc 好了
                        $fullcombo = new Imagick("./renders/08_MaimaiDX/images/fullcombo.png");
                        $im->compositeImage($fullcombo, $fullcombo->getImageCompose(), $instance_x+275,$row_y+80);
                    }
                }else{
                    $draw->annotation($instance_x+230,$row_y+72," ~ ");
                    $draw->setFillColor(new ImagickPixel('#000'));
                    $draw->setFontSize(22);
                    $draw->annotation($instance_x+200,$row_y+100," / ".$chara_games);
                }
                // draw unit

                if(file_exists("./images/chara/".$chara_key.".png")){
                    $im_icon_chara = new Imagick("./images/chara/".$chara_key.".png");
                    $im->compositeImage($im_icon_chara, $im_icon_chara->getImageCompose(), $instance_x+337,$row_y+37);
                }
                
            }
        }  
    }
      
//avatar
    $im_avatar = new Imagick($profileObejct["avatarfull"]);
    $im_avatar->roundCornersImage(25, 25);
// composite
    $im->compositeImage($im_avatar, $im_avatar->getImageCompose(), $profile_x-200, $profile_y-47);
    
    // Coop role levels
    $draw->setFontSize(40);
    $role_distance = 70;
    $role_x = $profile_x;
    $role_y = $profile_y+140;
    $draw -> setFillColor(new ImagickPixel('#E86D6D'));
    $draw -> annotation($role_x, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2A']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#5EB7DB'));
    $draw -> annotation($role_x + $role_distance*1, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2B']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#3CB371'));
    $draw -> annotation($role_x + $role_distance*2, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2C']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#FF8C00'));
    $draw -> annotation($role_x + $role_distance*3, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2D']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#BA55D3'));
    $draw -> annotation($role_x + $role_distance*4, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2E']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#FFD700'));
    $draw -> annotation($role_x + $role_distance*5, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2F']["value"]))/6));
    $draw->setFillColor(new ImagickPixel('#000'));//reset color
    
    //profile
    $s_level = $contentObject[3]["STAT_PLAYER_LEVEL"]["value"];
    $draw->setStrokeWidth(1);
    $draw->setFontSize(42);
    $draw->annotation($profile_x+62, $profile_y+92, $s_level);
    
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
    $draw->annotation($profile_x+665, $profile_y+2, "(".($total_games==0?0:round(100*$total_wins/$total_games,1))."%)");
    //bh合winrate
    $bh_games = $contentObject[3]["STAT_ONLINE_BH_GAMES_PLAYED"]["value"];
    $bh_wins = $contentObject[3]["STAT_ONLINE_BH_GAMES_WON"]["value"];
    $draw->annotation($profile_x+665, $profile_y+137, "(".($bh_games==0?0:round(100*$bh_wins/$bh_games,1))."%)");
    $draw->setFillColor(new ImagickPixel('#000'));
    $draw->setFontSize(48);
    $draw->annotation($profile_x+765, $profile_y+6, $contentObject[3]["STAT_ONLINE_GAMES_WON"]["value"]);
    $draw->annotation($profile_x+765, $profile_y+51, $contentObject[3]["STAT_ONLINE_GAMES_PLAYED"]["value"]);
    $draw->annotation($profile_x+765, $profile_y+96, $contentObject[3]["STAT_C_GAMES_PLAYED"]["value"]);
    $draw->annotation($profile_x+765, $profile_y+141, $bh_wins);
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
        $im->compositeImage($im_sp, $im_sp->getImageCompose(), $profile_x-57, $profile_y-67);
    }
    

// render
    echo $im->getImageBlob();
}
