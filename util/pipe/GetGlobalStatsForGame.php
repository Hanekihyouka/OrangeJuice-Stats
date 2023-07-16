<?php
require_once ("../StatJsonParser.php");
header("Content-Type: application/json;charset=utf-8");

$origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';

$allow_origin = array(
    'https://interface.100oj.com',
    'http://interface.100oj.com'
);

if(in_array($origin, $allow_origin)){
    header('Access-Control-Allow-Origin:'.$origin);
}
//CORS
//header('Access-Control-Allow-Origin: https://interface.100oj.com');  // 设为星号，表示同意任意跨源请求。也可配置特定的域名可访问 如:  https://www.xxxx.com
header('Access-Control-Allow-Methods:OPTIONS,POST,GET'); // 允许请求的类型
header('Access-Control-Allow-Headers:Content-Type,Content-Length,Accept-Encoding,X-Requested-with, Origin');
header('Access-Control-Expose-Headers:Content-Length,Content-Range');

if (isset($_GET["key"])){
    echo requestJsonGlobal41($_GET["key"]);
}
exit();
