<?php
//Включаем отладку php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

//Выключаем ограничение памяти php
ini_set('memory_limit', '6000M');

//Ограничение на выполнения скрипта
set_time_limit(30000);


//Подключаем библиотеки

/*
 * require_once 'simplehtmldom/simple_html_dom.php';
require_once 'functions.php';
*/

//композер
require_once 'vendor/autoload.php';

use Spatie\Async\Pool;


$httpKonkurent='https://iherb-russia.org';

$html = file_get_contents("$httpKonkurent/collection/all");

//Количество страниц
$nPages=str_get_html($html);
$nPages=$nPages->find('.pagination-link');
$nPages=$nPages[count($nPages)-1];
$nPages=$nPages->plaintext;
$nPages=trim($nPages);

//Забираем товары со страницы каталога, и внутренней страницы каталога
$products=productsGetFromCatalogPageKonkurent($html, $httpKonkurent);

//Запускаем асинхронность
$pool = Pool::create()->autoload('vendor/autoload.php')->concurrency(10);


for($i=2; $i<=$nPages; $i++){
    $pool[] = async(function () use ($httpKonkurent, $i, $products){
        $html = file_get_contents("$httpKonkurent/collection/all?page=$i");
        return productsGetFromCatalogPageKonkurent($html, $httpKonkurent);
    })->then(function ($output) {
        //
    })->catch(function (Exception $e) {
        $error=$e->getMessage();
        $error=explode('|||', $error);
        if(is_array($error)){
            print_r($error[0].PHP_EOL);
        }else{
            print_r($e->getMessage().PHP_EOL);
        }

        // Handle `MyException`
    });
}


await($pool);

//print_r($products);

/*
$products[$k]['title'] = $title;
$products[$k]['url'] = $linkProduct;
$products[$k]['productAbrBrand'] = rus2utf8($productAbrBrand);
$products[$k]['rating'] = $rating;
$products[$k]['vesOtpravkiUSA'] = $vesOtpravkiUSA;
$products[$k]['razmeriUSA'] = $razmeriUSA;

*/