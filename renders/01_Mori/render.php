<?php
function build($contentObject,$profileObejct){
    $im_w = 700;
    $im_h = 400;
    $im_avatar = new Imagick($profileObejct["avatarfull"]);
    //$im_avatar = new Imagick("./avatar184.jpg");
    $im = $im_avatar -> clone();
    $im_glass = new Imagick();
    $im_glass -> newImage($im_w,$im_h,new ImagickPixel('rgba(60,60,60,0.7)'));

// cut
    $im -> cropImage(175,100,4,42);
// Gaussian Blur
    $im -> blurImage(5,3);
// resize
    $im -> adaptiveResizeImage($im_w,$im_h);
// composite
    $im -> compositeImage($im_glass,  $im_glass->getImageCompose(), 0, 0);
// shadow and draw avatar
// make an avatar mask
    $avatar_mask = new Imagick();
    $avatar_mask -> newImage(220,300,new ImagickPixel('rgba(0,0,0,1)'));
    $avatar_mask -> setImageFormat('png');
    $avatar_mask -> shearImage(new ImagickPixel('rgba(255,255,255,0)'),15,0);//300*300
// put mask on avatar
    $im_avatar -> setImageFormat("png");
    $im_avatar -> adaptiveResizeImage(300,300);
    $im_avatar -> compositeImage($avatar_mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
// build shadow for avatar
    $shadow_layer = $im_avatar-> clone();
    $shadow_layer -> setImageBackgroundColor(new ImagickPixel('#555'));
    $shadow_layer -> shadowImage(250, 2, 0, 0);
    $shadow_layer -> compositeImage($im_avatar, Imagick::COMPOSITE_OVER, 0, 0);
    $im -> compositeImage($shadow_layer, imagick::COMPOSITE_DEFAULT, 40, 50);
// add otherGlasses
    addShearedGlass($im,$im_glass,'rgba(20,20,20,0.4)',330 ,50,300,130,15);
    addShearedGlass($im,$im_glass,'rgba(20,20,20,0.4)',330 - 70*tan(deg2rad(15)) ,200,300,50,15);// 70 = y2 + h2 - y1 -h1
    addShearedGlass($im,$im_glass,'rgba(20,20,20,0.4)',330 - 170*tan(deg2rad(15)) ,270,300,80,15);// 270 + 80 - 50 - 130
// Text
    $text = new Imagick();
    $text->newImage($im_w, $im_h, 'none');
    $text->setImageFormat('png');

    $draw = new ImagickDraw();
// Level && Online  Games  Played  &&  Coop  Games  Played
    $draw -> setFillColor(new ImagickPixel('#fff'));
    $draw -> setFont("./images/SAO.otf");
    $draw -> setFontSize(45);
    $draw -> annotation(370, 137, $contentObject[3]["STAT_PLAYER_LEVEL"]["value"]);
    $draw -> annotation(478, 92, $contentObject[3]["STAT_ONLINE_GAMES_PLAYED"]["value"]);
    $draw -> annotation(455, 157, $contentObject[3]["STAT_C_GAMES_PLAYED"]["value"]);


// NPC_DEFEATS
    $draw -> setFontSize(30);
    $draw -> annotation(400, 237, $contentObject[3]["STAT_NPC_DEFEATS"]["value"]);
// Coop role levels
    $draw -> setFontSize(37);
    $role_distance = 60;
    $role_x = 300;
    $role_y = 330;
    $draw -> setFillColor(new ImagickPixel('#F08080'));
    $draw -> annotation($role_x, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2A']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#87CEEB'));
    $draw -> annotation($role_x + $role_distance*1, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2B']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#98FB98'));
    $draw -> annotation($role_x + $role_distance*2, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2C']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#F4A460'));
    $draw -> annotation($role_x + $role_distance*3, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2D']["value"]))/6));
    $draw -> setFillColor(new ImagickPixel('#BA55D3'));
    $draw -> annotation($role_x + $role_distance*4, $role_y, floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2E']["value"]))/6));

// Titles
    $draw -> setFillColor(new ImagickPixel('#bbb'));
    $draw -> setFontSize(17);
    $draw -> annotation(370, 96, 'Level');
    $draw -> annotation(525, 110, 'Online  Games  Played');
    $draw -> annotation(507, 175, 'Coop  Games  Played');
    $draw -> annotation(410, $role_y - 40, 'Coop Role Levels');
// Title NPC
    $draw -> setFontSize(25);
    $draw -> annotation(475, 237, 'NPC Defeats');

// Nick  !!! Font
    if(isset($_GET["nick"])){
        $nick = $_GET["nick"];
    }else{
        $nick = $profileObejct["personaname"];
    }
    $draw -> setFont("./renders/01_Mori/images/ARHei.ttf");
    //sp1 for sp Nick Color
    $sp1 = 0;
    if(isset($_GET["sp1"])){
        $sp1 = intval($_GET["sp1"]);
    }
    switch($sp1){
        case 1:
            $draw -> setFillColor(new ImagickPixel('#F4A460'));
            break;
        case 2:
            $draw -> setFillColor(new ImagickPixel('#98FB98'));
            break;
        case 3:
            $draw -> setFillColor(new ImagickPixel('#87CEEB'));
            break;
        case 4:
            $draw -> setFillColor(new ImagickPixel('#BA55D3'));
            break;
        default:
            $draw -> setFillColor(new ImagickPixel('#fff'));
    }
    $nick_font = 35;
    $draw -> setFontSize($nick_font);
    $nick_width = ($text -> queryFontMetrics($draw, $nick))["textWidth"];
    for (;$nick_width > 600;$nick_font--){
        $draw -> setFontSize($nick_font);
        $nick_width = ($text -> queryFontMetrics($draw, $nick))["textWidth"];
    }
    addShearedGlass($im,$im_glass,'rgba(20,20,20,0.4)',-20 ,12,$nick_width+26,41,15);
    $draw -> annotation(4, 47, $nick);


// rende text
    $text -> drawImage($draw);
    $im -> compositeImage($text,  $text->getImageCompose(), 0, 0);


// render
    echo $im->getImageBlob();

}

function addGlass($im,$im_glass,$color='rgba(20,20,20,0.4)',$x=0,$y=0,$w=0,$h=0){
    // new glass for stats
    $im_glass -> clear();
    $im_glass -> newImage($w,$h,new ImagickPixel($color));
    // add new glass
    // and shadow
    $shadow_layer = $im_glass -> clone();
    $shadow_layer -> setImageBackgroundColor(new ImagickPixel('#555'));
    $shadow_layer -> shadowImage(160, 2, 0, 0);
    $shadow_layer -> compositeImage($im_glass, Imagick::COMPOSITE_OVER, 0, 0);
    $im -> compositeImage($shadow_layer, imagick::COMPOSITE_DEFAULT, $x, $y);
}

function addShearedGlass($im,$im_glass,$color='rgba(20,20,20,0.4)',$x=0,$y=0,$w=0,$h=0,$shearX=0,$shearY=0){//
    // new glass for stats
    $im_glass -> clear();
    $im_glass -> newImage($w,$h,new ImagickPixel($color));
    $im_glass -> shearimage(new ImagickPixel('rgba(0,0,0,0)'), $shearX, $shearY);
    // add new glass
    // and shadow
    $shadow_layer = $im_glass -> clone();
    $shadow_layer -> setImageBackgroundColor(new ImagickPixel('#555'));
    $shadow_layer -> shadowImage(160, 2, 0, 0);
    $shadow_layer -> compositeImage($im_glass, Imagick::COMPOSITE_OVER, 0, 0);
    $im -> compositeImage($shadow_layer, imagick::COMPOSITE_DEFAULT, $x, $y);
}
