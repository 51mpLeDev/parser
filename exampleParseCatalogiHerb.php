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

$page=getPageFromiHerb("https://www.iherb.com/c/{$catalogsName[0]}");

//print_r($page); die;

$html = str_get_html($page);

$productCards = $html->find('.products')[0];

$productCards = $productCards->find('.product-cell-container');

//print_r((string)$productCards[0]); die;

$products = [];


foreach ($productCards as $k => $v) {

    $title=$v->find('.product-title')[0]->plaintext;

    $linkProduct=$v->find('.absolute-link-wrapper')[0]->find('a')[0]->href;

    $productIdInIHerb=explode('/', $linkProduct);
    $productIdInIHerb=$productIdInIHerb[count($productIdInIHerb)-1];

    $productAbrBrand=$v->find('.absolute-link-wrapper')[0]->find('a')[0]->attr['data-ga-brand-id'];

    $productFullBrand=$v->find('.absolute-link-wrapper')[0]->find('a')[0]->attr['data-ga-brand-name'];

    $partNumber=$v->find('.absolute-link-wrapper')[0]->find('a')[0]->attr['data-part-number'];

    $rating=$v->find('.stars')[0]->attr['title'];

    $priceDíscount=$v->find('.price-olp');
    if(!empty($priceDíscount)){
        $priceDíscount=$priceDíscount[0]->find('bdi')[0]->plaintext;
    }else{
        $priceDíscount='';
    }

    $priceUSA=$v->find('.price')[0]->find('bdi')[0]->plaintext;

    $variativniyTovar=$v->find('.more-options-available-wrapper')[0];
    if(delSpace($variativniyTovar->plaintext)!=''){
        $variativniyTovar=1;
    }else{
        $variativniyTovar=0;
    }

    $variateArray=[];
    $nVariate=0;
    if($variativniyTovar){
        $page = getPageFromiHerb($linkProduct);
        $html = str_get_html($page);

        $variationsTmp=$v->find('.product-grouping-row');

        if(count($variationsTmp)==1){
            $variationsTmp=$variationsTmp[0]->find('.attribute-tile');
            foreach($variationsTmp as $varKey => $varVal) {
                /*
                 * $link
                 * $getlink
                 * $nameVariate
                 * $oldPrice
                 * $newPrice
                 * pushNewProduct
                 * Если есть цена у вариации то забираем вариацию
                 * Исключить повторный парсинг вариаций!
                 *
                 * */
                if(count($varVal->find('bdi'))>0){
                    $variateArray[$varKey]['name']=$varVal->attr['data-val'];
                    $variateArray[$varKey]['url']=$varVal->attr['data-url'];
                    $variateArray[$varKey]['artikul']=$varVal->attr['data-pid'];

                    $varPrice=$varVal->find('.price-container')[0];
                    $varPrice=$varPrice->find('bdi');
                    if(count($varPrice)==1){
                        $varPrice=$varPrice[0]->plaintext;
                        $varPriceDiscount='';
                    }elseif(count($varPrice)==2){
                        $varPriceDiscount=$varPrice[0]->plaintext;
                        $varPrice=$varPrice[1]->plaintext;
                    }

                    $variateArray[$varKey]['price']=$varPrice;
                    $variateArray[$varKey]['priceDiscount']=$varPriceDiscount;

                    if($variateArray[$varKey]['url']!=$linkProduct){
                        $varHtml=getPageFromiHerb($variateArray[$varKey]['url']);
                        $varHtml=str_get_html($varHtml);

                        /*
                         * собираем вариацию и пушим в продукты, продумать как лучше
                         * может по коду товара проверить есть ли такой товар
                         * и только потом решать что с ним делать
                         *
                        */
                        $varPropertiesTmp=$varHtml->find('.product-grouping-row')[0]->find('#modelProperties')[0];

                        $varTitleUSA=$varPropertiesTmp->attr['data-product-name'];

                        $varProductIdUSA=$varPropertiesTmp->attr['data-product-id'];

                        $varProductAbrBrand=$varPropertiesTmp->attr['data-brand-code'];

                        $varProductFullBrand=$varPropertiesTmp->attr['data-brand-name'];

                        $varPartNumber=$varPropertiesTmp->attr['data-part-number'];

                        $varRatingUSA=$rating;

                        $varPriceDíscountUSA=$varPropertiesTmp->attr['data-discounted-price'];

                        $varPriceUSA=$varPropertiesTmp->attr['data-list-price'];




                        $varProperties=[];
                        $varProperties['varTitleUSA']=$varTitleUSA;
                        $varProperties['urlUSA']=$variateArray[$varKey]['url'];
                        $varProperties['productIdUSA']=$varProductIdUSA;
                        $varProperties['productAbrBrand']=$varProductAbrBrand;
                        $varProperties['productFullBrand']=$varProductFullBrand;
                        $varProperties['partNumber']=$varPartNumber;
                        $varProperties['ratingUSA']=$varRatingUSA;
                        $varProperties['priceDíscountUSA']=$varPriceDíscountUSA;
                        $varProperties['priceUSA']=$varPriceUSA;
                        $varProperties['variativniyTovar']=1;
                        $varProperties['variateArray']=$variateArray;

                    }




                }else{
                    continue;
                }
            }
        }elseif (count($variationsTmp)==2){
            while ($variateRunWhile){

            }
        }else{
            print_r("много вариаций $linkProduct"); die;
        }
        $variateRunWhile=true;






    }



    $products[$k]['titleUSA'] = delSpace(rus2utf8($title));
    $products[$k]['urlUSA'] = $linkProduct;
    $products[$k]['productIdUSA'] = $productIdInIHerb;
    $products[$k]['productAbrBrand'] = rus2utf8($productAbrBrand);
    $products[$k]['productFullBrand'] = rus2utf8($productFullBrand);
    $products[$k]['partNumber'] = $partNumber;
    $products[$k]['ratingUSA'] = $rating;
    $products[$k]['priceDíscountUSA'] = $priceDíscount;
    $products[$k]['priceUSA'] = $priceUSA;
    $products[$k]['variativniyTovar'] = $variativniyTovar;
    $products[$k]['variateArray'] = $variateArray;



    /*
    $products[$k]['smallImgForCatalog'] = $smallImgForCatalog;
    $products[$k]['descrHtmlProductForCatalog'] = $descrHtmlProductForCatalog;
    $products[$k]['srokDeistvia'] = $srokDeistvia;
    $products[$k]['vesOtpravki'] = $vesOtpravki;
    $products[$k]['vesOtpravkiUSA'] = $vesOtpravkiUSA;
    $products[$k]['kolvoVUpakovke'] = $kolvoVUpakovke;
    $products[$k]['razmeri'] = $razmeri;
    $products[$k]['razmeriUSA'] = $razmeriUSA;
    $products[$k]['variativniyTovar'] = $variativniyTovar;
    */
}



/*
foreach ($products as $k => $v) {

}*/




print_r($products);