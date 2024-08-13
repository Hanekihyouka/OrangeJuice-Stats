<?php
function build($contentObject,$profileObejct){
    $im_w = 454;
    $im_h = 136;

//background
    $im = new Imagick("./renders/04_TentekiKo/images/back.png");
    $im->roundCornersImage(4, 4);
//avatar
    $im_avatar = new Imagick($profileObejct["avatarfull"]);
    $im_avatar->roundCornersImage(4, 4);
    $im_avatar->resizeImage(121, 121, 0.9, 1, true);
// composite
    $im->compositeImage($im_avatar, $im_avatar->getImageCompose(), 8, 8);
// sp1
    $sp1 = 0;
    if(isset($_GET["sp1"])){
        $sp1 = intval($_GET["sp1"]);
    }
    if($sp1>0){
        $im_sp_file = "./images/sp1_".$sp1.".png";
        $im_sp = new Imagick($im_sp_file);
        $im_sp->resizeImage(35, 35, 0.9, 1, true);
        $im->compositeImage($im_sp, $im_sp->getImageCompose(), 412, 7);
    }
// face
    //排序
    arsort($contentObject[0]);
    $im_face_name = getFaceName(key($contentObject[0]));
    $im_face_index = "_00_03.png";
    $im_face_file = "./images/face/".$im_face_name.$im_face_index;
    if(!file_exists($im_face_file)){
        $im_face_index = "_00_00.png";
        $im_face_file = "./images/face/".$im_face_name.$im_face_index;
    }
    if(!file_exists($im_face_file)){
        $im_face_index = "_00_01.png";
        $im_face_file = "./images/face/".$im_face_name.$im_face_index;
    }
    if(!file_exists($im_face_file)){
        $im_face_file = "./images/face/"."silhouette_00.png";
    }
    $im_face = new Imagick($im_face_file);
    $im_face->resizeImage(46, 46, 0.9, 1, true);
    if($sp1>0){
        $im->compositeImage($im_face, $im_face->getImageCompose(), 360, 4);
    }else{
        $im->compositeImage($im_face, $im_face->getImageCompose(), 402, 4);
    }
// Text
    $text = new Imagick();
    $text->newImage($im_w, $im_h, 'none');
    $text->setImageFormat('png');

    $draw = new ImagickDraw();

    // info
    $draw->setFillColor(new ImagickPixel('#fff'));
    $draw -> setFont("./images/ResourceHanRoundedCN-Medium.ttf");
    // nick
    if(isset($_GET["nick"])){
        $nick = $_GET["nick"];
    }else{
        $nick = $profileObejct["personaname"];
    }
    $draw->setFontSize(27);
    $draw->annotation(137, 34, $nick);
    // Level && Online  Games  Win && xp++/level
    $draw->setFont("./images/RockoUltraFLF.ttf");
    $draw->setFillColor(new ImagickPixel('#666'));
    $draw->setFontSize(19);
    $draw->annotation(143, 67, 'Level');
    $draw->annotation(143, 90, 'Online Wins');
    $draw->annotation(143, 113, 'Online Played');
    // Alignment  -->> right align
    $draw->setTextAlignment(3);
    $s_level = $contentObject[3]["STAT_PLAYER_LEVEL"]["value"];
    $s_lxp = $contentObject[3]["STAT_PLAYER_EXP"]["value"];
    $p_rate = 1 - ((1300 + 100*$s_level)*$s_level/2 - $s_lxp) / (600+100*$s_level);
    $draw->annotation(440, 67, "(".round(100*$p_rate,1)."%)");
    //$draw->annotation(440 - 70, 67, "(");
    $draw->annotation(440 - 85, 67, $s_level);
    //pvp合winrate
    $total_games = 0;
    $total_wins = 0;
    foreach ($contentObject[0] as $thisStat){
        $total_games += $thisStat["games"];
        $total_wins += $thisStat["wins"];
    }
    $draw->annotation(440, 90, "(".($total_games==0?0:round(100*$total_wins/$total_games,1))."%)");
    //$draw->annotation(440 - 70, 90, "(");
    $draw->annotation(440 - 85, 90, $contentObject[3]["STAT_ONLINE_GAMES_WON"]["value"]);
    $draw->annotation(440, 113, $contentObject[3]["STAT_ONLINE_GAMES_PLAYED"]["value"]);

// rende text
    $text->drawImage($draw);
    $im->compositeImage($text, $text->getImageCompose(), 0, 0);


// render
    echo $im->getImageBlob();
}

