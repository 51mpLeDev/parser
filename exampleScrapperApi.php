<?php

//Включаем отладку php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

//Выключаем ограничение памяти php
ini_set('memory_limit', '4000M');

//Ограничение на выполнения скрипта
set_time_limit(300);


//Подключаем библиотеки
require 'functions.php';

/*
 * Получаем и присваиваем куки
 */
$params=[];
$params['api']='d029c4eccd74c5b20f149f4e6a30c179';
$params['url']=urlencode('https://iherb.com');

$params['userHeadersInsert']='&keep_headers=true';
$params['userHeadersInsert']='';

$url = "https://api.scraperapi.com/?api_key={$params['api']}&url={$params['url']}&retry_404=true&autoparse=true&country_code=us&device_type=desktop{$params['userHeadersInsert']}";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, TRUE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$response = curl_exec($ch);


curl_close($ch);
//вывод на монитор

preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
$tmpCookies = array();
foreach($matches[1] as $item) {
    parse_str($item, $cookie);
    $tmpCookies = array_merge($tmpCookies, $cookie);
}

//$tmpCookies['language']='ru-RU';
print_r($tmpCookies);
$cookies='';

foreach($tmpCookies as $k => $v){
    $cookies.="$k=$v; ";
}


/*
 * Получаем и присваиваем куки
 */
$params=[];
$params['api']='d029c4eccd74c5b20f149f4e6a30c179';
$params['url']=urlencode('https://iherb.com');

$params['userHeadersInsert']='&keep_headers=true';

$url = "https://api.scraperapi.com/?api_key={$params['api']}&url={$params['url']}&retry_404=true&autoparse=true&country_code=us&device_type=desktop{$params['userHeadersInsert']}";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, TRUE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_COOKIE, "$cookies");
$response = curl_exec($ch);


curl_close($ch);
//вывод на монитор

/*
preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
$tmpCookies = array();
foreach($matches[1] as $item) {
    parse_str($item, $cookie);
    $tmpCookies = array_merge($tmpCookies, $cookie);
}


$cookies='';

foreach($tmpCookies as $k => $v){
    $cookies.="$k=$v; ";
}*/



print_r($response);
//print_r($response);

