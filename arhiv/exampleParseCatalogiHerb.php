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
require 'simplehtmldom/simple_html_dom.php';
require 'functions.php';

$catalogsName = ['supplements', 'sports', 'bath-personal-care', 'beauty', 'grocery', 'healthy-home', 'baby-kids', 'pets'];

/*foreach ($catalogsName as $catalog){

}*/

$page=getPageFromiHerb($catalogsName[0]);

//print_r($page); die;

$html = str_get_html($page);

$productCards = $html->find('.products')[0];

$productCards = $productCards->find('.product-cell-container');

//print_r((string)$productCards[0]); die;

$products = [];


foreach ($productCards as $k => $v) {
    $linkProduct = $v->find('.product-title')[0]->find('a')[0]->href;

    $title = $v->find('.product-title')[0]->find('a')[0]->find('bdi')[0]->innertext;
    $title = rus2utf8($title);

    $smallImgForCatalog = '';
    if (count($v->find('.col-product-image')) > 0) {
        if (count($v->find('.col-product-image')[0]->find('img')) > 0) {
            $smallImgForCatalog = $v->find('.col-product-image')[0]->find('img')[0]->src;
        } else {
            $smallImgForCatalog = $v->find('.col-product-image')[0]->find('.js-defer-image')[0]->attr['data-image-src'];
            //print_r("Товар $title нуждается в дополнительном условии из за img".PHP_EOL);
        }
    } else {
        print_r("Товар $title нуждается в дополнительном условии из за .col-product-image");
    }

    $productIdInIHerb = $v->find('.product-title')[0]->find('a')[0]->attr['data-product-id'];

    $smallImgForCatalogPartNumber = $v->find('.product-title')[0]->find('a')[0]->attr['data-part-number'];

    $productAbrBrand = $v->find('.product-title')[0]->find('a')[0]->attr['data-ga-brand-id'];

    $productFullBrand = $v->find('.product-title')[0]->find('a')[0]->attr['data-ga-brand-name'];

    $rating = $v->find('.rating')[0]->find('a')[0]->attr['title'];
    $rating = explode('/', $rating)[0];

    $descHtml = $v->find('.col-product-title')[0]->find('ul')[0];
    $tempHtml = $descHtml->find('li');
    $tempHtmlArray = [];
    $srokDeistvia = '';
    $vesOtpravki = '';
    $kolvoVUpakovke = '';
    foreach ($tempHtml as $item) {
        //Удаляем элемент див (вроде не несет огромной полезности) в каждой строке таблицы
        if ($srokDeistvia == '') {
            $srokDeistvia = strpos(rus2utf8((string)$item), 'Срок действия');
        }
        if ($vesOtpravki == '') {
            $vesOtpravki = strpos(rus2utf8((string)$item), 'Вес отправления');
        }
        if ($kolvoVUpakovke == '') {
            $kolvoVUpakovke = strpos(rus2utf8((string)$item), 'Количество в упаковке');
        }
        $razmeri = strpos(rus2utf8((string)$item), 'Размеры');

        if ($srokDeistvia and gettype($srokDeistvia) != "string") {
            $div = (string)$item->find('div')[0];
            $item = (string)$item;
            $item = str_replace($div, '', $item);
            $item = rus2utf8($item);
            $item = str_get_html($item);
            $item = $item->plaintext;
            $item = trim($item);
            $item = delSpace($item);
            $srokDeistvia = explode(': ', $item)[1];
            array_push($tempHtmlArray, "Срок годности до: $srokDeistvia");
        } elseif ($vesOtpravki and gettype($vesOtpravki) != "string") {
            $vesOtpravkiUSA = rus2utf8($item->find('.toggle-weight-preference')[0]->attr['data-imperial']);
            $div = (string)$item->find('div')[0];
            $item = (string)$item;
            $item = str_replace($div, '', $item);
            $item = rus2utf8($item);
            $item = str_get_html($item);
            $item = $item->plaintext;
            $item = trim($item);
            $item = delSpace($item);
            array_push($tempHtmlArray, "$item");
            $vesOtpravki = explode(': ', $item)[1];
        } elseif ($kolvoVUpakovke and gettype($kolvoVUpakovke) != "string") {
            $item = rus2utf8($item);
            $item = str_get_html($item);
            $item = $item->plaintext;
            $item = trim($item);
            $item = delSpace($item);
            array_push($tempHtmlArray, "$item");
            $kolvoVUpakovke = explode(': ', $item)[1];
        } elseif ($razmeri and gettype($razmeri)) {
            $item = rus2utf8($item);
            $item = str_get_html($item);
            $razmeriUSA = $item->find('.toggle-weight-preference')[0]->attr['data-imperial'];
            //По скольку американский вес парсится не правильный из кода iherb.ru, формируем американский вес из размеров упаковки
            $vesOtpravkiUSA = explode(', ', $razmeriUSA)[1];
            $razmeri = $item->find('.toggle-weight-preference')[0]->attr['data-metric'];
            array_push($tempHtmlArray, "Размеры: $razmeri");
        } else {
            //Остальные данные, кроме стандартных.
            print_r(rus2utf8((string)$item));
            die;
            $div = (string)$item->find('div')[0];
            $item = (string)$item;
            $item = str_replace($div, '', $item);
            $item = rus2utf8($item);
            $item = str_get_html($item);
            $item = $item->plaintext;
            $item = trim($item);
            $item = delSpace($item);
            array_push($tempHtmlArray, $item);
        }
    }
    $descrHtmlProductForCatalog = '';
    foreach ($tempHtmlArray as $item) {
        $descrHtmlProductForCatalog .= "<li>{$item}</li>";
    }
    $descrHtmlProductForCatalog = "<ul>$descrHtmlProductForCatalog</ul>";
    if (trim($v->find('.more-options-available-wrapper')[0]->plaintext) == '') {
        $variativniyTovar = 0;
    } else {
        $variativniyTovar = 1;
    }
    //print_r($item); die;


    $products[$k]['title'] = $title;
    $products[$k]['url'] = $linkProduct;
    $products[$k]['smallImgForCatalog'] = $smallImgForCatalog;
    $products[$k]['productIdInIHerb'] = $productIdInIHerb;
    $products[$k]['smallImgForCatalogPartNumber'] = $smallImgForCatalogPartNumber;
    $products[$k]['productAbrBrand'] = rus2utf8($productAbrBrand);
    $products[$k]['productFullBrand'] = rus2utf8($productFullBrand);
    $products[$k]['rating'] = $rating;
    $products[$k]['descrHtmlProductForCatalog'] = $descrHtmlProductForCatalog;
    $products[$k]['srokDeistvia'] = $srokDeistvia;
    $products[$k]['vesOtpravki'] = $vesOtpravki;
    $products[$k]['vesOtpravkiUSA'] = $vesOtpravkiUSA;
    $products[$k]['kolvoVUpakovke'] = $kolvoVUpakovke;
    $products[$k]['razmeri'] = $razmeri;
    $products[$k]['razmeriUSA'] = $razmeriUSA;
    $products[$k]['variativniyTovar'] = $variativniyTovar;
}

print_r($products);