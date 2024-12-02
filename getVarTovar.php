<?php

//Включаем отладку php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

//Выключаем ограничение памяти php
ini_set('memory_limit', '4000M');

//Ограничение на выполнения скрипта
set_time_limit(30000);



//Подключаем библиотеки
require 'simplehtmldom/simple_html_dom.php';
require 'functions.php';

$categoryName = ['supplements', 'sports', 'bath-personal-care', 'beauty', 'grocery', 'healthy-home', 'baby-kids', 'pets'];

foreach ($categoryName as $category){
    $dir=__DIR__;

    //Очищаем базу
    file_put_contents("{$dir}/mainUSA.txt", '');
    parseCategoryUSA($category);
}




/*
foreach ($products as $k => $v) {

}*/




//print_r($products);