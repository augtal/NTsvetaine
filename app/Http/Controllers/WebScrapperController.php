<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;

#lenteles i kurias bus saugojama info
use App\Models\Advertisement;
use App\Models\AdvertisementLocation;
use App\Models\AdvertisementDetails;
use App\Models\AdvertisementPrices;

#nekilnojamo turto svetainiu sarasas
use App\Models\REWebPages;
use App\Models\REWebsites;

class WebScrapperController extends Controller
{
    private $DomoAdsPerPage = 30;
    private $NTportalasAdsPerPage = 20;

    public function index(){
        $REWebsiteList = REWebsites::all();

        foreach($REWebsiteList as $website){
            $this->scrape($website);
        }

        echo "End of scraping";
    }

    public function summonMainMethod(){
        echo "Test method";
        return;
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
                if( strpos($info['url'], 'domoplius') !== FALSE){
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

        #lenteles info kambarių skaicius, buto plotas ir t.t
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
        $prices->save();
    }

    private function updateAdvertisementPrices($adsPrice, $id){
        #updates old price updated_at field, by imitating a change
        AdvertisementPrices::where('advertisement_id', $id)->orderBy('id', 'desc')->first()->touch();

        #sets new price
        $newPrice = new AdvertisementPrices();
        $newPrice->advertisement_id = $id;
        $newPrice->price = $adsPrice;
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
}