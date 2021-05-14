<?php

namespace App\Http\Controllers;


use App\Traits\FindNotificationsTrait;
use App\Traits\CreateUserMessageTrait;

use Illuminate\Http\Request;
use Goutte\Client;

#lenteles i kurias bus saugojama info
use App\Models\Advertisement;
use App\Models\AdvertisementLocation;
use App\Models\AdvertisementDetails;
use App\Models\AdvertisementPrices;
use App\Models\Notification;
use App\Models\NotificationAdvertisements;
#nekilnojamo turto svetainiu sarasas
use App\Models\REWebPages;
use App\Models\REWebsites;

class WebScrapperController extends Controller
{
    use FindNotificationsTrait;
    use CreateUserMessageTrait;

    private $DomoAdsPerPage = 30;
    private $NTportalasAdsPerPage = 25;
    private $CapitalAdsPerPage = 20;

    private $limiter = 1;

    public function index(){
        //$REWebsiteList = REWebsites::all();

        $website = REWebsites::find(2);
        $websitePages = REWebPages::where('r_e_websites_id', $website->id)->first();

        //$this->scrapeNtportalasAll($websitePages);

        /*
        foreach($REWebsiteList as $website){
            $this->scrape($website);
        }
        */

        $notifications = Notification::all();

        foreach($notifications as $notification){
            //1 Kai atsiranda naujas skelbimas zonoje
            if($notification->frequency == 1){
                $amount = $this->findAdsInsideNotification($notification);
                if($notification->advertisement_count < $amount){
                    $messageAddon = "Atsirano naujas skelbimas.";
                    $this->createNewMessage($notification, $messageAddon);

                    $notification->advertisement_count = $amount;
                    $notification->save();
                }
            }
            //2 Kada pasikeicia skelbimu zonoje kaina
            elseif($notification->frequency == 2){
                $advertisementList = NotificationAdvertisements::where('notification_id', $notification->id)->get();

                foreach($advertisementList as $singleAdvertisement){
                    $id = $singleAdvertisement->advertisement_id;
                    $prices = AdvertisementPrices::where('advertisement_id', $singleAdvertisement->advertisement_id)->orderBy('updated_at', 'desc')->orderBy('created_at', 'desc')->take(2)->get()->toArray();

                    if($prices[0]['price'] != $prices[1]['price']){
                        $messageAddon = "Pasikeite skelbimo kaina.";
                        $this->createNewMessage($notification, $messageAddon);
                        break;
                    }
                }
            }
        }

        echo "End of scraping";
    }

    private function scrape($website){
        $websitePages = REWebPages::where('r_e_websites_id', $website->id)->get();
        echo "Start scraping:" . "<br>";
        //domoplius svetaine
        if($website->title == 'Domoplius'){
            foreach($websitePages as $REWebPage){
                echo "<br>";
                echo "Advertisements from: " . $REWebPage['url'];
                echo "<br>";
                echo "============================================";
                $this->scrapeDomoAll($REWebPage);
            }
        }
        elseif($website->title == 'NTportalas'){
            foreach($websitePages as $REWebPage){
                echo "<br>";
                echo "Advertisements from: " . $REWebPage['url'];
                echo "<br>";
                echo "============================================";
                $this->scrapeNtportalasAll($REWebPage);
            }
        }
        elseif($website->title == 'Capital'){
            foreach($websitePages as $REWebPage){
                echo "<br>";
                echo "Advertisements from: " . $REWebPage['url'];
                echo "<br>";
                echo "============================================";
                $this->scrapeCapitalAll($REWebPage);
            }
        }
    }

    private function scrapeNtportalasAll($REWebPage){
        $client = new Client();
        $crawler = $client->request('GET', $REWebPage['url']);

        $adAmount = (int)$crawler->filter('div#center > div:nth-child(3) > div > b > span')->text();

        $a=0;

        if($adAmount % $this->NTportalasAdsPerPage > 0) $pages = (int)($adAmount / $this->NTportalasAdsPerPage + 1);
        else $pages = $adAmount / $this->NTportalasAdsPerPage;

        #------------------------------------------------------------------------------------------------------------------------------------------------
        #remove limiter
        if($this->limiter != 0) $pages = $this->limiter;
        #------------------------------------------------------------------------------------------------------------------------------------------------
        
        for ($i = 1; $i <= $pages; $i++) {
            $url = substr($REWebPage['url'], 0, -1) . $i;
            $crawler = $client->request('GET', $url);

            $adsInfo = $this->getNtportalasPageAdsInfo($crawler);

            foreach($adsInfo as $info){
                $l = "";
                if( strpos($info['url'], 'capital') == TRUE){
                    #patikrinti ar skelbimas is sitos svetaines jau yra
                    $adID = Advertisement::where('title', $info['title'])->where('area', $info['area'])->where('r_e_websites_id', 2)->first();
                    if($adID != null){
                        #atnaujinti kaina
                        $adID->touch();
                        AdvertisementDetails::where('advertisement_id', $adID->id)->first()->touch();
                        $this->updateAdvertisementPrices($info['price'], $adID->id);
                        $l = "U |";
                    }
                    else{
                        #sukurti nauja
                        $result = $this->scrapeCapitalSingle($client, $info['url']);
                        $detailedInfo = $this->fixResultsCapital($result);

                        $advertisement = $this->insertToAdvertisement($REWebPage, $info, $detailedInfo);
                        $this->downloadAdvertisementThumbnail($advertisement);
                        $this->insertToAdvertisementLocation($detailedInfo, $advertisement->id);
                        $this->insertToAdvertisementDetails($detailedInfo, $advertisement->id);
                        $this->insertToAdvertisementPrices($info, $advertisement->id);
                        $l = "C |";
                    }
                    
                }
                $a += 1;
                echo $l . " finished " . $a . '<br>';
            }
        }

        return;
    }

    private function getNtportalasPageAdsInfo($crawler){
        $adsInfo = Array();

        $crawler->filter('div#center')->children()->each(function ($node) use (&$adsInfo){
            $info = Array();

            $titleCity = $node->filter('div.center-box-cont > div.local')->text('empty');

            dd('halted');

            if($titleCity != 'empty'){
                $description = explode(",", $node->filter('div.realty-item-description > div.rid-additional')->text());
                $info['title'] = $description[0] . " " . $titleCity;

                $info['url'] = $node->filter('a')->link()->getUri();
                
                $info['area'] = (double)substr($description[1], 0, -3);

                $price = $node->filter('div.realty-item-price > strong')->text();
                $price = substr($price, 0, -4); # to remove € with a space before 
                $price = str_replace(',', '', $price);
                $info['price'] = (int)$price;
                if($node->filter('div.realty-item-image')->count()){
                    $imgURL = $node->filter('div.realty-item-image')->attr('style');
                    $imgURL = explode("'", $imgURL);

                    $info['imgUrl'] = $imgURL[count($imgURL)-2];
                }
                else{
                    $info['imgUrl'] = "";
                }

                array_push($adsInfo, $info);
            }
        });
        return $adsInfo;
    }

    private function scrapeCapitalSingle2($client, $link){
        $results = Array();
        $crawler = $client->request('GET', $link);

        #visa skelbimo info ==========
        $info = array();
        $crawler->filter('table.realty-main-info > tbody > tr.realty-main-info-top')->siblings()->each(function ($item) use (&$info) {
            $info[$item->filter('td:nth-child(1)')->text()] = $item->filter('td:nth-child(2)')->text();
        });

        $results = $info;


        #aprasymas ==============
        if($crawler->filter('div.realty-information-container.col-md-6 > div.realty-description')->count()){
            $description = explode('<a', $crawler->filter('div.realty-information-container.col-md-6 > div.realty-description')->html())[0];#issaugomas su <br>
        }
        else{
            $description = "";
        }
        $results['description'] = $description;

        #lng/lat
        $locationURL = $crawler->filter('div.realty-image.realty-image-map.popup-open > div#location-popup.popup-data > div.realty-iframe-buttons > a')->link()->getUri();
        if (preg_match_all("/\d{1,3}\.\d{1,6}/", $locationURL, $values)){
            $results['lat'] = (double)$values[0][0];
            $results['lng'] = (double)$values[0][1];
        }
        else{
        }
        

        return $results;
    }


    private function scrapeCapitalAll($REWebPage){
        $client = new Client();
        $crawler = $client->request('GET', $REWebPage['url']);

        $adAmountText = explode("(", $crawler->filter('div.realty-items-container.col-md-9 > div.realty-items-top > div.left-side > div.serch-results > strong')->text());
        $adAmountText = $adAmountText[count($adAmountText)-1];
        $adAmount = (int)substr($adAmountText, 0, strlen($adAmountText)-1);

        $a=0;

        if($adAmount % $this->CapitalAdsPerPage > 0) $pages = (int)($adAmount / $this->CapitalAdsPerPage + 1);
        else $pages = $adAmount / $this->CapitalAdsPerPage;

        #------------------------------------------------------------------------------------------------------------------------------------------------
        #remove limiter
        if($this->limiter != 0) $pages = $this->limiter;
        #------------------------------------------------------------------------------------------------------------------------------------------------
        
        for ($i = 1; $i <= $pages; $i++) {
            $url = substr($REWebPage['url'], 0, -1) . $i;
            $crawler = $client->request('GET', $url);

            $adsInfo = $this->getCapitalPageAdsInfo($crawler);

            foreach($adsInfo as $info){
                $l = "";
                if( strpos($info['url'], 'capital') == TRUE){
                    #patikrinti ar skelbimas is sitos svetaines jau yra
                    $adID = Advertisement::where('title', $info['title'])->where('area', $info['area'])->where('r_e_websites_id', 2)->first();
                    if($adID != null){
                        #atnaujinti kaina
                        $adID->touch();
                        AdvertisementDetails::where('advertisement_id', $adID->id)->first()->touch();
                        $this->updateAdvertisementPrices($info['price'], $adID->id);
                        $l = "U |";
                    }
                    else{
                        #sukurti nauja
                        $result = $this->scrapeCapitalSingle($client, $info['url']);
                        $detailedInfo = $this->fixResultsCapital($result);

                        $advertisement = $this->insertToAdvertisement($REWebPage, $info, $detailedInfo);
                        $this->downloadAdvertisementThumbnail($advertisement);
                        $this->insertToAdvertisementLocation($detailedInfo, $advertisement->id);
                        $this->insertToAdvertisementDetails($detailedInfo, $advertisement->id);
                        $this->insertToAdvertisementPrices($info, $advertisement->id);
                        $l = "C |";
                    }
                    
                }
                $a += 1;
                echo $l . " finished " . $a . '<br>';
            }
        }

        return;
    }

    private function getCapitalPageAdsInfo($crawler){
        $adsInfo = Array();

        $crawler->filter('div.realty-box > div.realty-items-container.col-md-9 > div.realty-items')->children()->each(function ($node) use (&$adsInfo){
            $info = Array();

            $titleCity = $node->filter('div.realty-item-description > div.rid-place')->text('empty');

            if($titleCity != 'empty'){
                $description = explode(",", $node->filter('div.realty-item-description > div.rid-additional')->text());
                $info['title'] = $description[0] . " " . $titleCity;

                $info['url'] = $node->filter('a')->link()->getUri();
                
                $info['area'] = (double)substr($description[1], 0, -3);

                $price = $node->filter('div.realty-item-price > strong')->text();
                $price = substr($price, 0, -4); # to remove € with a space before 
                $price = str_replace(',', '', $price);
                $info['price'] = (int)$price;
                if($node->filter('div.realty-item-image')->count()){
                    $imgURL = $node->filter('div.realty-item-image')->attr('style');
                    $imgURL = explode("'", $imgURL);

                    $info['imgUrl'] = $imgURL[count($imgURL)-2];
                }
                else{
                    $info['imgUrl'] = "";
                }

                array_push($adsInfo, $info);
            }
        });
        return $adsInfo;
    }

    private function scrapeCapitalSingle($client, $link){
        $results = Array();
        $crawler = $client->request('GET', $link);

        #visa skelbimo info ==========
        $info = array();
        $crawler->filter('table.realty-main-info > tbody > tr.realty-main-info-top')->siblings()->each(function ($item) use (&$info) {
            $info[$item->filter('td:nth-child(1)')->text()] = $item->filter('td:nth-child(2)')->text();
        });

        $results = $info;


        #aprasymas ==============
        if($crawler->filter('div.realty-information-container.col-md-6 > div.realty-description')->count()){
            $description = explode('<a', $crawler->filter('div.realty-information-container.col-md-6 > div.realty-description')->html())[0];#issaugomas su <br>
        }
        else{
            $description = "";
        }
        $results['description'] = $description;

        #lng/lat
        $locationURL = $crawler->filter('div.realty-image.realty-image-map.popup-open > div#location-popup.popup-data > div.realty-iframe-buttons > a')->link()->getUri();
        if (preg_match_all("/\d{1,3}\.\d{1,6}/", $locationURL, $values)){
            $results['lat'] = (double)$values[0][0];
            $results['lng'] = (double)$values[0][1];
        }
        else{
        }
        

        return $results;
    } 

    private function fixResultsCapital($oldResults){
        $fixed = Array();

        if(array_key_exists('Adresas', $oldResults)) 
            $fixed['adress'] = $oldResults['Adresas'];
        else 
            $fixed['adress'] = "Nera";

        if(array_key_exists('Kambariai', $oldResults)) 
            $fixed['rooms'] = $oldResults['Kambariai'];
        else 
            $fixed['rooms'] = "Nera";

        if(array_key_exists('Aukštas', $oldResults)){
            $floorsArray = explode('/', $oldResults['Aukštas']);
            $fixedFloor = $floorsArray[0] . ', ' . $floorsArray[1] . " aukštų pastate";
            $fixed['floor'] = $fixedFloor;
        }
        else 
            $fixed['floor'] = "Nera";

        if(array_key_exists('Statinio tipas', $oldResults)) 
            $fixed['buildingType'] = $oldResults['Statinio tipas'];
        else 
            $fixed['buildingType'] = "Nera";

        if(array_key_exists('Šildymas', $oldResults)) 
            $fixed['heating'] = $oldResults['Šildymas'];
        else 
            $fixed['heating'] = "Nera";

        if(array_key_exists('Statybos metai', $oldResults)) 
            $fixed['year'] = $oldResults['Statybos metai'];
        else 
            $fixed['year'] = "Nera";

        if(array_key_exists('description', $oldResults)) 
            $fixed['description'] = $oldResults['description'];
        else 
            $fixed['description'] = "Nera";

        if(array_key_exists('lng', $oldResults)) 
            $fixed['lng'] = $oldResults['lng'];
        else 
            $fixed['lng'] = 0.0;

        if(array_key_exists('lat', $oldResults)) 
            $fixed['lat'] = $oldResults['lat'];
        else 
            $fixed['lat'] = 0.0;

        return $fixed;
    }

    private function scrapeDomoAll($REWebPage){
        $client = new Client();

        $crawler = $client->request('GET', $REWebPage['url']);

        $adAmount = (int)substr($crawler->filter('div.cntnt-box-fixed > div.listing-title > span')->text(), 1, -1);
        
        $a=0;

        if($adAmount % $this->DomoAdsPerPage > 0) $pages = (int)($adAmount / $this->DomoAdsPerPage + 1);
        else $pages = $adAmount / $this->DomoAdsPerPage;

        #------------------------------------------------------------------------------------------------------------------------------------------------
        #remove limiter
        $pages = 1;
        #------------------------------------------------------------------------------------------------------------------------------------------------
        
        for ($i = 1; $i <= $pages; $i++) {
            $url = substr($REWebPage['url'], 0, -1) . $i;
            $crawler = $client->request('GET', $url);

            $adsInfo = $this->getDomoPageAdsInfo($crawler);

            foreach($adsInfo as $info){
                $l = "";
                if( strpos($info['url'], 'domoplius') == TRUE){
                    #patikrinti ar skelbimas is sitos svetaines jau yra
                    $adID = Advertisement::where('title', $info['title'])->where('area', $info['area'])->where('r_e_websites_id', 1)->first();
                    if($adID != null){
                        #atnaujinti kaina
                        $adID->touch();
                        AdvertisementDetails::where('advertisement_id', $adID->id)->first()->touch();
                        $this->updateAdvertisementPrices($info['price'], $adID->id);
                        $l = "U |";
                    }
                    else{
                        #sukurti nauja
                        $result = $this->scrapeDomoSingle($client, $info['url']);
                        $detailedInfo = $this->fixResultsDomo($result);
                        $advertisement = $this->insertToAdvertisement($REWebPage, $info, $detailedInfo);
                        $this->downloadAdvertisementThumbnail($advertisement);
                        $this->insertToAdvertisementLocation($detailedInfo, $advertisement->id);
                        $this->insertToAdvertisementDetails($detailedInfo, $advertisement->id);
                        $this->insertToAdvertisementPrices($info, $advertisement->id);
                        $l = "C |";
                    }
                    
                }
                $a += 1;
                echo $l . " finished " . $a . '<br>';
            }
        }
    }

    private function getDomoPageAdsInfo($crawler){
        $adsInfo = Array();

        $crawler->filter('main > div.cntnt-box-fixed > ul.auto-list')->children()->each(function ($node) use (&$adsInfo){
            $info = Array();
            $info['title'] = $node->filter('.item-section.fr > h2 > a')->text('e');
            if($info['title'] != 'e'){
                $info['url'] = $node->filter('.item-section.fr > h2 > a')->link()->getUri();

                $info['area'] = (double)substr($node->filter('.item-section.fr > div.param-list > div > span')->text(), 0, -3);

                $price = $node->filter('.item-section.fr > div.price > p.fl > strong')->text();
                $price = substr($price, 0, -4); # to remove € with a space before 
                $price = str_replace(' ', '', $price);
                $info['price'] = (int)$price;
                if($node->filter('div > div.thumb.fl > a > img')->count()){
                    $info['imgUrl'] = $node->filter('div > div.thumb.fl > a > img')->attr('src');
                }
                else{
                    $info['imgUrl'] = "";
                }

                array_push($adsInfo, $info);
            }
        });
        return $adsInfo;
    }

    private function scrapeDomoSingle($client, $link){
        $results = Array();
        $crawler = $client->request('GET', $link);

        #==========================================================

        #Adresas 
        $adress = '';
        $crawler->filter('div.breadcrumb > div.breadcrumb-item')->each(function ($item) use (&$adress) {
            $adress = $adress . $item->filter('a > span')->text() . ', ';
        });

        $results['adress'] = $adress;

        #lenteles info: kambarių skaicius, aukstas, namoTipas, sildymas, pastatymo metai
        $tableInfo = Array();
        $crawler->filter('div.medium.info-block > table.view-group')->each(function ($node) use (&$tableInfo) {
            $node->filter('tr')->each(function ($item) use (&$tableInfo){
                $tableInfo[$item->filter('th')->text()] = $item->filter('td')->text();
            });
            
        });

        $results = array_merge($results, $tableInfo);

        #aprasymas
        $amount = $crawler->filter('div.col-right > div.medium.info-block')->children()->count();
        $descriptionMarker = "div.col-right > div.medium.info-block > div:nth-child(" . $amount - 3 . ")"; # - 3, nes komentarai yra 3 nuo galo <br><div><div>
        if($crawler->filter($descriptionMarker)->count()){
            $description = $crawler->filter($descriptionMarker)->html();#issaugomas su <br>
        }
        else{
            $description = "";
        }
        $results['description'] = $description;

        #lng/lat
        $mapLink = $crawler->filter('a#mini-map-block')->link()->getUri();
        $crawler = $client->request('GET', $mapLink);

        $lngAndLat = $crawler->filter('#container > section > div.small-wrapper > div.content-wrapper > main > script:nth-child(5)')->text();
        if (preg_match_all("/\d{1,3}\.\d{1,6}/", $lngAndLat, $values)){
            $results['lat'] = (double)$values[0][0];
            $results['lng'] = (double)$values[0][1];
        }
        else{
        }

        return $results;
    } 

    private function fixResultsDomo($oldResults){
        $fixed = Array();

        if(array_key_exists('adress', $oldResults)) 
            $fixed['adress'] = substr($oldResults['adress'], 0, -2);
        else 
            $fixed['adress'] = "Nera";

        if(array_key_exists('Kambarių skaičius:', $oldResults)) 
            $fixed['rooms'] = $oldResults['Kambarių skaičius:'];
        else 
            $fixed['rooms'] = "Nera";

        if(array_key_exists('Aukštas:', $oldResults)){
            $fixed['floor'] = $oldResults['Aukštas:'];
        }
        else 
            $fixed['floor'] = "Nera";

        if(array_key_exists('Namo tipas:', $oldResults)) 
            $fixed['buildingType'] = $oldResults['Namo tipas:'];
        else 
            $fixed['buildingType'] = "Nera";

        if(array_key_exists('Šildymas:', $oldResults)) 
            $fixed['heating'] = $oldResults['Šildymas:'];
        else 
            $fixed['heating'] = "Nera";

        if(array_key_exists('Statybos metai:', $oldResults)) 
            $fixed['year'] = $oldResults['Statybos metai:'];
        else 
            $fixed['year'] = "Nera";

        if(array_key_exists('description', $oldResults)) 
            $fixed['description'] = $oldResults['description'];
        else 
            $fixed['description'] = "Nera";

        if(array_key_exists('lng', $oldResults)) 
            $fixed['lng'] = $oldResults['lng'];
        else 
            $fixed['lng'] = 0.0;

        if(array_key_exists('lat', $oldResults)) 
            $fixed['lat'] = $oldResults['lat'];
        else 
            $fixed['lat'] = 0.0;

        return $fixed;
    }

    private function insertToAdvertisement($REWebPage, $adsInfo, $detailedInfo){
        $advertisement = new Advertisement();

        $advertisement->title = $adsInfo['title'];
        $advertisement->category = $REWebPage['category'];
        $advertisement->type = $REWebPage['type'];
        $advertisement->area = $adsInfo['area'];
        $advertisement->adress = $detailedInfo['adress'];
        $advertisement->r_e_websites_id = $REWebPage['r_e_websites_id']; 
        $advertisement->thumbnail = $adsInfo['imgUrl'];
        $advertisement->url = $adsInfo['url'];
        $advertisement->save();

        return $advertisement;
    }

    private function insertToAdvertisementLocation($detailedInfo, $id){
        $location = new AdvertisementLocation();

        $location->advertisement_id = $id;
        $location->lat = $detailedInfo['lat'];
        $location->lng = $detailedInfo['lng'];
        $location->save();
    }

    private function insertToAdvertisementDetails($detailedInfo, $id){
        $details = new AdvertisementDetails();

        $details->advertisement_id = $id;
        $details->rooms = $detailedInfo['rooms'];
        $details->floor = $detailedInfo['floor'];
        $details->buildingType = $detailedInfo['buildingType'];
        $details->heating = $detailedInfo['heating'];
        $details->year = $detailedInfo['year'];
        $details->description = $detailedInfo['description'];
        $details->save();
    }

    private function insertToAdvertisementPrices($adsInfo, $id){
        $prices = new AdvertisementPrices();

        $prices->advertisement_id = $id;
        $prices->price = $adsInfo['price'];
        $prices->priceChange = 0;
        $prices->save();
    }

    private function updateAdvertisementPrices($adsPrice, $id){
        #updates old price updated_at field, by imitating a change
        $oldPrice = AdvertisementPrices::where('advertisement_id', $id)->orderBy('id', 'desc')->first();
        $oldPrice->touch();

        $changeAmount = round((($adsPrice * 100) / $oldPrice) - 100, 1);

        #sets new price
        $newPrice = new AdvertisementPrices();
        $newPrice->advertisement_id = $id;
        $newPrice->price = $adsPrice;
        $newPrice->priceChange = $changeAmount;
        $newPrice->save();
    }

    private function downloadAdvertisementThumbnail($advertisement) {
        if(strlen($advertisement->thumbnail) > 0){
            $ch = curl_init();
        
            curl_setopt($ch, CURLOPT_URL, $advertisement->thumbnail);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, false);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
            $data = curl_exec($ch);
            curl_close($ch);

            $extension = explode(".", $advertisement->thumbnail);
            $fileName = "" . $advertisement->id . "." . $extension[count($extension)-1];

            file_put_contents("images/AdvertisementsThumbnails/" . $fileName, $data);
        }
    }


    // tester code (depricated)
    public function summonMainMethod(){
        echo "Test method";
        echo "<br>";

        $oldPrice = 32500.00;
        $adsPrice = 32500.00;
        $changeAmount = round((($adsPrice * 100) / $oldPrice) - 100, 1);

        echo $changeAmount . "%";

        /*
        $imgURL = 'https://www.capital.lt/image/catalog/capital_logo.png';
        $imgID = 3;
        $this->downloadWebsiteLogo($imgURL, $imgID);
        return;
        */
    }

    private function downloadWebsiteLogo($url, $id) {
        if(strlen($url) > 0){
            $ch = curl_init();
        
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, false);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
            $data = curl_exec($ch);
            curl_close($ch);

            $extension = explode(".", $url);
            $fileName = "" . $id . "." . $extension[count($extension)-1];

            file_put_contents("images/RealEstateWebsiteLogos/" . $fileName, $data);
        }
    }
}
