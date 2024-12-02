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
$pageLink="https://www.iherb.com/c/{$catalogsName[0]}";

$page=testDownloadAndDownload($pageLink);

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

    $priceDiscount=$v->find('.price-olp');
    if(!empty($priceDiscount)){
        $priceDiscount=$priceDiscount[0]->find('bdi')[0]->plaintext;
    }else{
        $priceDiscount='';
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
        $page = testDownloadAndDownload($linkProduct);

        $html = str_get_html($page);

        $variationsTmp=$html->find('.product-grouping-row');

        //echo count($variationsTmp); die;

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
                /*if(count($varVal->find('bdi'))>0){*/
                    $variateArray[$varKey]['name']=$varVal->attr['data-val'];
                    $variateArray[$varKey]['url']=$varVal->attr['data-url'];
                    $variateArray[$varKey]['artikul']=$varVal->attr['data-pid'];

                    /*$varPrice=$varVal->find('.price-container')[0];
                    $varPrice=$varPrice->find('bdi');
                    if(count($varPrice)==1){
                        $varPrice=$varPrice[0]->plaintext;
                        $varPriceDiscount='';
                    }elseif(count($varPrice)==2){
                        $varPriceDiscount=$varPrice[0]->plaintext;
                        $varPrice=$varPrice[1]->plaintext;
                    }

                    $variateArray[$varKey]['price']=$varPrice;
                    $variateArray[$varKey]['priceDiscount']=$varPriceDiscount;*/


                    $varHtml=testDownloadAndDownload($variateArray[$varKey]['url']);
                    $varHtml=str_get_html($varHtml);

                    /*
                     * собираем вариацию и пушим в продукты, продумать как лучше
                     * может по коду товара проверить есть ли такой товар
                     * и только потом решать что с ним делать
                     *
                    */
                    $varPropertiesTmp=$varHtml->find('#modelProperties')[0];

                    $varTitleUSA=$varPropertiesTmp->attr['data-product-name'];

                    $varProductIdUSA=$varPropertiesTmp->attr['data-product-id'];

                    $varProductAbrBrand=$varPropertiesTmp->attr['data-brand-code'];

                    $varProductFullBrand=$varPropertiesTmp->attr['data-brand-name'];

                    $varPartNumber=$varPropertiesTmp->attr['data-part-number'];

                    $varRatingUSA=$rating;

                    $varPriceDiscountUSA=$varPropertiesTmp->attr['data-discounted-price'];

                    $varPriceUSA=$varPropertiesTmp->attr['data-list-price'];

                    $available=(string)$varPropertiesTmp->attr['data-available-to-purchase'];

                    $variateArray[$varKey]['price']=$varPriceUSA;
                    $variateArray[$varKey]['priceDiscountUSA']=$varPriceDiscountUSA;
                    $variateArray[$varKey]['available']=$available;
                    $variateArray[$varKey]['partNumber']=$varPartNumber;

                    $varProperties=[];
                    $varProperties['titleUSA']=delSpace(rus2utf8($varTitleUSA));
                    $varProperties['urlUSA']=$variateArray[$varKey]['url'];
                    $varProperties['productIdUSA']=$varProductIdUSA;
                    $varProperties['productAbrBrand']=rus2utf8($varProductAbrBrand);
                    $varProperties['productFullBrand']=rus2utf8($varProductFullBrand);
                    $varProperties['partNumber']=$varPartNumber;
                    $varProperties['ratingUSA']=$varRatingUSA;
                    $varProperties['priceDiscountUSA']=$varPriceDiscountUSA;
                    $varProperties['priceUSA']=$varPriceUSA;
                    $varProperties['variativniyTovar']=1;
                    $varProperties['variateArray']=$variateArray;

                    joinRusAndUsa($varProperties);






                /*}else{
                    continue;
                }*/
            }
        }elseif(count($variationsTmp)==2){
            $startVars=$variationsTmp[0];
            $nameStartVars=$startVars->childNodes()[0]->plaintext;
            $nameEndVars=$variationsTmp[1]->childNodes()[0]->plaintext;

            $startVars=$startVars->childNodes()[2]->childNodes();
            $urlsStartVars=[];

            foreach ($startVars as $startVar){
                $urlsStartVars[]=[
                    'nameVar'=>$startVar->attr['data-val'],
                    'urlVar'=>$startVar->attr['data-url']
                ];
            }


            foreach ($urlsStartVars as $urlStartVar){
                $pageStartVar=testDownloadAndDownload($urlStartVar['urlVar']);
                $pageStartVar=str_get_html($pageStartVar);
                $productsVar=$pageStartVar->find('.product-grouping-row')[1];

                //если вариационный товар не имеет доступных вариаций, то нужно исключить такую вариацию
                $varParseBackArray=varParse($productsVar, $rating, $urlStartVar['urlVar']);


                if(count($varParseBackArray)>0){

                    $variateArray[]=[
                        'nameVar' => $urlStartVar['nameVar'],
                        'array' => $varParseBackArray
                    ];

                }



            }
            //print_r($variateArray); die;




        }elseif(count($variationsTmp)==3){
            $QuantityDiscount=TRUE;

            $count=count($variationsTmp)-1;
            $endVars=$variationsTmp[$count];
            $endVars=$endVars->find('label')[0]->plaintext;

            if(str_contains($endVars, 'Quantity Discount')){
                $startVars=$variationsTmp[0];
                $nameStartVars=$startVars->childNodes()[0]->plaintext;
                $nameEndVars=$variationsTmp[1]->childNodes()[0]->plaintext;

                $startVars=$startVars->childNodes()[2]->childNodes();
                $urlsStartVars=[];

                foreach ($startVars as $startVar){
                    $urlsStartVars[]=[
                        'nameVar'=>$startVar->attr['data-val'],
                        'urlVar'=>$startVar->attr['data-url']
                    ];
                }
                //print_r($urlsStartVars); echo $linkProduct;  die;

                foreach ($urlsStartVars as $urlStartVar){
                    $pageStartVar=testDownloadAndDownload($urlStartVar['urlVar']);
                    $pageStartVar=str_get_html($pageStartVar);

                    $productsVar=$pageStartVar->find('.product-grouping-row')[1];

                    //print_r((string)$productsVar); echo $urlStartVar['urlVar']; die;

                    //если вариационный товар не имеет доступных вариаций, то нужно исключить такую вариацию
                    $varParseBackArray=varParse($productsVar, $rating, $urlStartVar['urlVar'], 1);

                    /*if(!empty($varParseBackArray) and str_contains($urlStartVar['urlVar'], 'california-gold-nutrition-collagenup-hydrolyzed-marine-collagen-peptides-with-hyaluronic-acid-and-vitamin-c-unflavored-7-26-oz-206-g/64903')){
                        print_r($varParseBackArray); print_r($urlStartVar['urlVar']); die;
                    }*/

                    if(count($varParseBackArray)>0){

                        $variateArray[]=[
                            'nameVar' => $urlStartVar['nameVar'],
                            'array' => $varParseBackArray
                        ];


                    }



                }
                //print_r($variateArray); echo $linkProduct;  die;
            }else{
                print_r($endVars); print_r($linkProduct.PHP_EOL);
            }

        }elseif(count($variationsTmp)>3){
            print_r($endVars); print_r($linkProduct.PHP_EOL);
        }







    }

    //Надо будет добавить условие что если есть вариации, и продукт имеет пустой список
    //Разобраться почему не пушиться все вариации

    if(str_contains($linkProduct, 'california-gold-nutrition-gold-c-usp-grade-vitamin-c')){
        print_r($variateArray); print_r($urlStartVar['urlVar']); die;
    }

    $products[$k]['titleUSA'] = delSpace(rus2utf8($title));
    $products[$k]['urlUSA'] = $linkProduct;
    $products[$k]['productIdUSA'] = $productIdInIHerb;
    $products[$k]['productAbrBrand'] = rus2utf8($productAbrBrand);
    $products[$k]['productFullBrand'] = rus2utf8($productFullBrand);
    $products[$k]['partNumber'] = $partNumber;
    $products[$k]['ratingUSA'] = $rating;
    $products[$k]['priceDiscountUSA'] = $priceDiscount;
    $products[$k]['priceUSA'] = $priceUSA;
    $products[$k]['variativniyTovar'] = $variativniyTovar;
    $products[$k]['variateArray'] = $variateArray;



    joinRusAndUsa($products[$k]);



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




//print_r($products);