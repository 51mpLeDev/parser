<?php

function varParse($varArray, $rating, $link, $quantityDiscount=0){

    $variations=$varArray->find('.attribute-tile');
    $variationsTmp=[];


    foreach($variations as $v){
        if(/*$v->attr['data-is-out-of-stock']!='True' and */str_contains((string)$v, 'Available in other')!==true){
            $variationsTmp[]=$v;
        }
    }




    $variateArray=[];
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
        $variateArray[$varKey]['priceDiscount']=$varPriceDiscount;
        */


        $varHtml=testDownloadAndDownload($variateArray[$varKey]['url']);
        $varHtml=str_get_html($varHtml);

        /*
         * собираем вариацию и пушим в продукты, продумать как лучше
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
        $variateArray[$varKey]['partNumber']=$varPartNumber;
        $variateArray[$varKey]['priceDiscountUSA']=$varPriceDiscountUSA;
        $variateArray[$varKey]['available']=$available;


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
        $varProperties['available']=$available;

        $varProperties['variativniyTovar']=1;
        //Не может быть что вариации пушатся из прошлого ключа вариаций возможно надо сохранять их в файл и брать от туда же
        $varProperties['variateArray']=$variateArray;

        joinRusAndUsa($varProperties);

        /*
         * И так тут $variateArray есть
         * */

        /*if($quantityDiscount!=0){
            print_r($variateArray); print_r($variateArray[$varKey]['url']); die;
        }*/


        /*}else{
            continue;
        }*/
    }

    /*if(str_contains($link, 'california-gold-nutrition-collagenup-hydrolyzed-marine-collagen-peptides-with-hyaluronic-acid-and-vitamin-c-unflavored-16-37-oz-464-g/64902')){
        foreach($variations as $v){
            if($v->attr['data-is-out-of-stock']!='True'){
                $variationsTmp[]=(string)$v;
            }
        }
        print_r($variateArray); die;
    }else{

    }*/


    return $variateArray;
}

function getPageFromiHerb($url){
    $findCode='countryCode: "US"';
    $boolCode='false';
    $dir=__DIR__;


    $url=urlencode($url);
    $url = "https://api.scraperapi.com/?api_key=d029c4eccd74c5b20f149f4e6a30c179&url={$url}&country_code=us&device_type=desktop&retry_404=true";
    while($boolCode == 'false'){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);

        if (str_contains($response, $findCode)) {
            $boolCode='true';
            //sleep(rand(5, 10));
        }elseif(str_contains($response, 'countryCode: "RU"')){
            file_put_contents("{$dir}/errorCountryCode.txt", "$url|||countryCode: 'RU".PHP_EOL, FILE_APPEND);
            //sleep(rand(5, 10));
            //print_r('countryCode: "RU"'.PHP_EOL);
        }elseif(str_contains($response, 'countryCode: "')){
            file_put_contents("{$dir}/errorCountryCode.txt", "$url|||countryCode: Иного государства".PHP_EOL, FILE_APPEND);
            //sleep(rand(5, 10));
            //print_r('countryCode: "Иного государства"'.PHP_EOL);
        }elseif(str_contains($response, 'https://www.scraperapi.com/support')){
            echo $url.'|||'.$response.PHP_EOL;
            //sleep(rand(5, 10));
        }else{
            print_r($url); print_r($response); die;
        }
    }
    return $response;
}

function rus2utf8($string){
    //$codirovka=mb_detect_encoding($string); php определила не правильно

    //Перебор во всех кодировках

    /*foreach(mb_list_encodings() as $chr){

        echo mb_convert_encoding($string, 'UTF-8', $chr)." : ".$chr."<br>";

    }*/

    return mb_convert_encoding($string, 'UTF-8', 'HTML-ENTITIES');

}

/*
 * $varProperties=[];
$varProperties['varTitleUSA']=$varTitleUSA;
$varProperties['urlUSA']=$variateArray[$varKey]['url'];
$varProperties['productIdUSA']=$varProductIdUSA;
$varProperties['productAbrBrand']=$varProductAbrBrand;
$varProperties['productFullBrand']=$varProductFullBrand;
$varProperties['partNumber']=$varPartNumber;
$varProperties['ratingUSA']=$varRatingUSA;
$varProperties['priceDiscountUSA']=$varPriceDiscountUSA;
$varProperties['priceUSA']=$varPriceUSA;
$varProperties['variativniyTovar']=1;
$varProperties['variateArray']=$variateArray;
 *
 *
 * */
function joinRusAndUsa($tovar){
    $dir=__DIR__;
    $dirKonkurentArray="{$dir}/baseFromKonkurent/{$tovar['partNumber']}.txt";
    $boolKonkurentArray=file_exists($dirKonkurentArray);

    $dirJoinedArray="{$dir}/BDunitedRusa/{$tovar['partNumber']}.txt";

    $dirNoAvailableKonkurent="{$dir}/bdFromUsa/NoAvailableKonkurent/{$tovar['partNumber']}.txt";

    //Если есть конкурентный массив
    if($boolKonkurentArray){
        $konkurentArray=file_get_contents($dirKonkurentArray);
        $konkurentArray=json_decode($konkurentArray,true);
        $newArray=$konkurentArray;

        $newArray['varTitleUSA']=$tovar['titleUSA'];
        $newArray['urlUSA']=$tovar['urlUSA'];
        $newArray['productIdUSA']=$tovar['productIdUSA'];
        $newArray['productAbrBrand']=$tovar['productAbrBrand'];
        $newArray['productFullBrand']=$tovar['productFullBrand'];
        $newArray['partNumber']=$tovar['partNumber'];
        $newArray['ratingUSA']=$tovar['ratingUSA'];
        $newArray['priceDiscountUSA']=$tovar['priceDiscountUSA'];
        $newArray['priceUSA']=$tovar['priceUSA'];
        $newArray['variativniyTovar']=$tovar['variativniyTovar'];
        $newArray['variativniyTovarUSA']=$tovar['variateArray'];

        $newArray=json_encode($newArray);
        file_put_contents($dirJoinedArray,$newArray);

    }else{
        $tovar=json_encode($tovar);
        file_put_contents($dirNoAvailableKonkurent, $tovar);
    }

}

function testDownloadAndDownload($url)
{

    $dir=__DIR__;
    $boolGetedPageLinkFile="{$dir}/bdFromUsa/index.txt";

    $boolGetedPage=0;

    $handle = @fopen($boolGetedPageLinkFile, "r");
    if ($handle)
    {
        while (!feof($handle))
        {
            $buffer = fgets($handle);
            $explUrl=explode('iherb.com', $url)[1];
            if(strpos($buffer, "$explUrl|||") !== FALSE){
                $boolGetedPage = $buffer;
            }
        }
        fclose($handle);
    }

    if($boolGetedPage == 0){
        $page=getPageFromiHerb($url);
        $n=count(file($boolGetedPageLinkFile));
        $pageIndex=serialize([$n, "|||$url|||"]);
        file_put_contents($boolGetedPageLinkFile, "{$pageIndex}".PHP_EOL, FILE_APPEND);
        file_put_contents("{$dir}/bdFromUsa/pages/{$n}.html", "$page");
        return $page;
    }else{
        $boolGetedPage=unserialize($boolGetedPage);
        $page=file_get_contents("{$dir}/bdFromUsa/pages/{$boolGetedPage[0]}.html");
        return $page;
    }
}

function delSpace($string){
    $string = html_entity_decode($string);
    $string = preg_replace('/\s+/', ' ', $string);
    $string = preg_replace ( "!\s++!u", ' ', $string );
    $string =trim($string);
    return $string;
}

function toNumberInt($string)
{
    $string=intval(preg_replace('/[^0-9]+/', '', $string), 10);
    return $string;
}

/**
 * @throws Exception
 */
function productsGetFromCatalogPageKonkurent($html, $httpKonkurent){
    $dir=__DIR__;
    $pageCatalog=str_get_html($html);
    $pageCatalog=$pageCatalog->find('.product-preview-elem');
//print_r(count($pageCatalog)); die;
    $products=[];

    foreach ($pageCatalog as $k => $v) {

        $konkurentCatalogTitle=$v->find('.product-preview__title')[0]->find('a')[0]->plaintext;

        $konkurentCatalogImg=$v->find('.product-preview__photo')[0]->find('picture')[0];
        $konkurentCatalogImg=$konkurentCatalogImg->find('img')[0]->attr['data-src'];
        //print_r((string)$konkurentCatalogImg); die;

        $linkKonkurent=$v->find('.product-preview__title')[0]->find('a')[0]->href;

        $konkurentOldPrice=toNumberInt($v->find('.product-preview__price-old')[0]->plaintext);

        $konkurentCurrentPrice=toNumberInt($v->find('.product-preview__price-cur')[0]->plaintext);

        $haracteristicsProductKonkurentTmp=$v->find('.product-preview__property')[0]->find('li');
        $haracteristicsProductKonkurent=[];
        $kolvoVUpakovke='';
        $srokDeistvia='';
        foreach ($haracteristicsProductKonkurentTmp as $item) {
            $tmp=delSpace($item->plaintext);
            $tmp=trim($tmp);
            array_push($haracteristicsProductKonkurent, $tmp);
            $tmp=explode(': ', $tmp);
            if($tmp[0]=='Код продукта'){
                $kodProducta=$tmp[1];
            }elseif($tmp[0]=='Бренд'){
                $productFullBrand=$tmp[1];
            }elseif($tmp[0]=='Срок годности') {
                if (count($tmp) > 2) {
                    //throw new Exception("У товара {$kodProducta} что то со сроком годности|||");
                    $srokDeistvia = $tmp[2];
                }else{
                    $srokDeistvia = $tmp[1];
                }
            }elseif($tmp[0]=='Количество в упаковке'){
                $kolvoVUpakovke=$tmp[1];
            }elseif($tmp[0]=='Размер упаковки'){
                $razmeri=$tmp[1];
            }elseif($tmp[0]=='Вес упаковки'){
                $vesOtpravki=$tmp[1];
            }else{
                print_r('Странная характеристика'); die;
            }
        }

        /*if(!isset($srokDeistvia)){
            throw new Exception("У товара {$kodProducta} почему-то нету срока действия|||");
        }*/


        $products[$k]['konkurentCatalogTitle'] = $konkurentCatalogTitle;
        $products[$k]['konkurentCatalogImg'] = $konkurentCatalogImg;
        $products[$k]['linkKonkurent'] = $linkKonkurent;
        $products[$k]['konkurentOldPrice'] = $konkurentOldPrice;
        $products[$k]['konkurentCurrentPrice'] = $konkurentCurrentPrice;
        $products[$k]['kodProducta'] = $kodProducta;
        $products[$k]['productFullBrand'] = $productFullBrand;
        $products[$k]['srokDeistvia'] = $srokDeistvia;
        $products[$k]['kolvoVUpakovke'] = $kolvoVUpakovke;
        $products[$k]['razmeri'] = $razmeri;
        $products[$k]['vesOtpravki'] = $vesOtpravki;

    }




    $productsCatalogPage=$products;
    foreach($productsCatalogPage as $k => $v){

        $fileExistsBool=file_exists("{$dir}/baseFromKonkurent/{$products[$k]['kodProducta']}.txt");

        if($fileExistsBool===true){
            $testPriceTmp = file_get_contents("{$dir}/baseFromKonkurent/{$products[$k]['kodProducta']}.txt");
            $testPriceTmp = json_decode($testPriceTmp);
            //print_r($testPriceTmp);
            $konkurentOldPrice=$testPriceTmp->konkurentOldPrice;
            $konkurentCurrentPrice=$testPriceTmp->konkurentCurrentPrice;

            //Если файл товара есть, но изменилась цена, то меняем цену и сохраняем без захода на страницу товара.
            if($konkurentOldPrice!==$products[$k]['konkurentOldPrice'] or $konkurentCurrentPrice!==$products[$k]['konkurentCurrentPrice']){
                $testPriceTmp=(array)$testPriceTmp;
                $testPriceTmp['konkurentOldPrice']=$products[$k]['konkurentOldPrice'];
                $testPriceTmp['konkurentCurrentPrice']=$products[$k]['konkurentCurrentPrice'];
                file_put_contents("{$dir}/baseFromKonkurent/{$products[$k]['kodProducta']}.txt", json_encode($testPriceTmp));
            }
        }

        //Если файла товара нету, то начинаем парсинг страницы товара
        if($fileExistsBool===false) {

            $httpProductPage = "{$httpKonkurent}{$v['linkKonkurent']}";
            $htmlProduct = file_get_contents($httpProductPage);
            $httpProductPage = str_get_html($htmlProduct);

            $categoryKonkurentTmp = $httpProductPage->find('.breadcrumb-link');
            $categoryKonkurent = [];
            foreach ($categoryKonkurentTmp as $item) {
                $item = $item->plaintext;
                $categoryKonkurent[] = $item;
            }
            $categoryKonkurent = implode(' -> ', $categoryKonkurent);

            $imgsHtmlTmp = $httpProductPage->find('.product__slide-main');
            $imgsBig = [];
            foreach ($imgsHtmlTmp as $item) {
                $item = $item->find('a')[0]->href;
                $imgsBig[] = $item;
            }

            $imgsSmallHtmlTmp = $httpProductPage->find('.product__slide-tumbs');
            $imgsSmall = [];
            foreach ($imgsSmallHtmlTmp as $item) {
                $item = $item->find('img')[0]->src;
                $imgsSmall[] = $item;
            }

            $articulTmp = explode('"sku": "', $htmlProduct);
            $articulTmp = explode('",\n"offers', $articulTmp[1]);
            $articulTmp = $articulTmp[0];
            $articulTmp = explode('",', $articulTmp);
            $articul = $articulTmp[0];

            $htmlDescriptionTmp = $imgsSmallHtmlTmp = $httpProductPage->find('.inner-content')[0]->first_child()->first_child();
            $htmlDescriptionKonkurent = (string)$htmlDescriptionTmp;

            $variativniyTovarTmp = strpos($htmlProduct, 'Варианты товара:');
            if ($variativniyTovarTmp !== false) {
                $variateArray = [];
                $variativniyTovarTmp = $httpProductPage->find('.cut-block__content-wrapper')[0]->find('td');
                foreach ($variativniyTovarTmp as $item) {
                    $linkTmp = $item->find('a');
                    if (count($linkTmp) > 0) {
                        //Пишем программу которая вместо array читает его значения
                        $link = $linkTmp[0]->href;
                        $text = $linkTmp[0]->plain;
                        $variateArray[] = ['name' => $text, 'link' => $link];
                    }
                }
                $variativniyTovar = $variateArray;
            } else {
                $variativniyTovar = 0;
            }


            $products[$k]['categoryKonkurent'] = $categoryKonkurent;
            $products[$k]['imgsBig'] = $imgsBig;
            $products[$k]['imgsSmall'] = $imgsSmall;
            $products[$k]['productIdInIHerb'] = $articul;
            $products[$k]['htmlDescriptionKonkurent'] = $htmlDescriptionKonkurent;
            $products[$k]['variativniyTovar'] = $variativniyTovar;


            file_put_contents("{$dir}/baseFromKonkurent/{$products[$k]['kodProducta']}.txt", json_encode($products[$k]));
            //print_r($products[$k]); die;
        }

    }

    return 'ok';

    //return $products;
}

function parseCategoryUSA($category){
    $thisPaginationCols=0; //Незабыть после смены категории сбросить количество
    $maxPaginationCols=999;
    $parseGo=true;

    while($parseGo){
        if($thisPaginationCols==0){
            $pageLink="https://www.iherb.com/c/{$category}";
        }else{
            $pageLink="https://www.iherb.com/c/{$category}?p={$thisPaginationCols}";
        }


        $page=testDownloadAndDownload($pageLink);

        $html = str_get_html($page);

        $maxPaginationCols=$html->find('.pagination-link');
        $maxPaginationCols=$maxPaginationCols[count($maxPaginationCols)-1]->plaintext;
        $maxPaginationCols=delSpace($maxPaginationCols);

        $productCards = $html->find('.products')[0];

        $productCards = $productCards->find('.product-cell-container');

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

            if(isset($v->find('.price')[0])){
                $priceUSA=$v->find('.price')[0]->find('bdi')[0]->plaintext;
            }elseif (isset($v->find('.see-in-cart-price')[0])){
                $priceUSA=$v->find('.see-in-cart-price')[0]->childNodes()[0]->plaintext;
                $priceUSA=delSpace($priceUSA);
            }else{
                echo "$linkProduct не имеет цены"; die;
            }


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

                        $variateArray[$varKey]['name']=$varVal->attr['data-val'];
                        $variateArray[$varKey]['url']=$varVal->attr['data-url'];
                        $variateArray[$varKey]['artikul']=$varVal->attr['data-pid'];

                        $varHtml=testDownloadAndDownload($variateArray[$varKey]['url']);
                        $varHtml=str_get_html($varHtml);

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


                        foreach ($urlsStartVars as $urlStartVar){
                            $pageStartVar=testDownloadAndDownload($urlStartVar['urlVar']);
                            $pageStartVar=str_get_html($pageStartVar);

                            $productsVar=$pageStartVar->find('.product-grouping-row')[1];

                            $varParseBackArray=varParse($productsVar, $rating, $urlStartVar['urlVar'], 1);

                            if(count($varParseBackArray)>0){

                                $variateArray[]=[
                                    'nameVar' => $urlStartVar['nameVar'],
                                    'array' => $varParseBackArray
                                ];


                            }



                        }
                    }else{
                        print_r($endVars); print_r($linkProduct.PHP_EOL);
                    }

                }elseif(count($variationsTmp)>3){
                    print_r($endVars); print_r($linkProduct.PHP_EOL);
                }

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
            $dir=__DIR__;
            file_put_contents("{$dir}/mainUSA.txt", serialize($products[$k]).PHP_EOL, FILE_APPEND);
        }
        if($thisPaginationCols==0){
            $thisPaginationCols=2;
        }else{
            $thisPaginationCols++;
        }
        if($thisPaginationCols==$maxPaginationCols){
            $parseGo=false;
        }
    }


}