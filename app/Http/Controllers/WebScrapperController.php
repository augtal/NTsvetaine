<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;

#lenteles i kurias bus saugojama info
use App\Models\Advertisement;
use App\Models\AdvertisementDetails;
use App\Models\AdvertisementPrices;

#nekilnojamo turto svetainiu sarasas
use App\Models\REWebPages;
use App\Models\REWebsites;

class WebScrapperController extends Controller
{
    public function index(){
        $REWebsiteList = REWebPages::all()->toArray();

        foreach($REWebsiteList as $website){
            $this->scrape($website);
        }

        echo "End of scraping";
    }
    private function scrape($website){
        $websiteName = REWebsites::where('id', $website['id'])->first()->toArray();

        //domoplius svetaine
        if($websiteName['title'] == 'Domoplius'){
            $this->scrapeDomoAll($website);
        }
    }

    private function scrapeDomoAll($website){
        $client = new Client();

        $crawler = $client->request('GET', $website['url']);

        $adAmount = (int)substr($crawler->filter('div.cntnt-box-fixed > div.listing-title > span')->text(), 1, -1);
        $adsPerPage = 30;
        
        $a=0;

        if($adAmount % $adsPerPage > 0) $pages = (int)($adAmount / $adsPerPage + 1);
        else $pages = $adAmount / $adsPerPage;

        #------------------------------------------------------------------------------------------------------------------------------------------------
        #remove limiter
        $pages = 1;
        #------------------------------------------------------------------------------------------------------------------------------------------------
        
        for ($i = 1; $i <= $pages; $i++) {
            $url = substr($website['url'], 0, -1) . $i;
            $crawler = $client->request('GET', $url);

            $adsInfo = $this->getPageAdsInfo($crawler);

            foreach($adsInfo as $info){
                $l = "";
                if( strpos($info['url'], 'domoplius') !== FALSE){
                    #check if add exists
                    $adID = Advertisement::where('title', $info['title'])->where('area', $info['area'])->first();
                    if($adID != null){
                        #update price
                        $adID->touch();
                        AdvertisementDetails::where('advertisement_id', $adID->id)->first()->touch();
                        $this->updateAdvertisementPrices($info['price'], $adID->id);
                        $l = "U |";
                    }
                    else{
                        #create new
                        $result = $this->scrapeDomoSingle($client, $info['url']);
                        $detailedInfo = $this->fixResultsDomo($result);
                        $id = $this->insertToAdvertisement($website, $info, $detailedInfo);
                        $this->insertToAdvertisementDetails($detailedInfo, $id);
                        $this->insertToAdvertisementPrices($info, $id);
                        $l = "C |";
                    }
                    
                }
                $a += 1;
                echo $l . " finished " . $a . '<br>';
            }
        
        }
    }

    private function getPageAdsInfo($crawler){
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
                
                $info['imgUrl'] = $node->filter('div > div.thumb.fl > a > img')->attr('src');

                array_push($adsInfo, $info);
            }
        });
        return $adsInfo;
    }

    private function insertToAdvertisement($website, $adsInfo, $detailedInfo){
        $advertisement = new Advertisement();

        $advertisement->title = $adsInfo['title'];
        $advertisement->category = $website['category'];
        $advertisement->type = $website['type'];
        $advertisement->area = $adsInfo['area'];
        $advertisement->r_e_websites_id = $website['r_e_websites_id']; 
        $advertisement->thumbnail = $adsInfo['imgUrl'];
        $advertisement->url = $adsInfo['url'];
        $advertisement->long = $detailedInfo['long'];
        $advertisement->lat = $detailedInfo['lat'];
        $advertisement->save();

        return $advertisement->id;
    }

    private function insertToAdvertisementDetails($detailedInfo, $id){
        $details = new AdvertisementDetails();

        $details->advertisement_id = $id;
        $details->adress = $detailedInfo['adress'];
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

    private function dwnImage($imgUrl, $advertID){
        $output_filename = $advertID;
        $host = $imgUrl;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        $fp = fopen($output_filename, 'wb');
        fwrite($fp, $result);
        fclose($fp);

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
        $description = $crawler->filter($descriptionMarker)->html();#issaugomas su <br>
        $results['description'] = $description;

        #long/lat
        $mapLink = $crawler->filter('a#mini-map-block')->link()->getUri();
        $crawler = $client->request('GET', $mapLink);

        $longAndLat = $crawler->filter('#container > section > div.small-wrapper > div.content-wrapper > main > script:nth-child(5)')->text();
        if (preg_match_all("/\d{1,3}\.\d{1,6}/", $longAndLat, $values)){
            $results['long'] = (double)$values[0][0];
            $results['lat'] = (double)$values[0][1];
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

        if(array_key_exists('long', $oldResults)) 
            $fixed['long'] = $oldResults['long'];
        else 
            $fixed['long'] = 0.0;

        if(array_key_exists('lat', $oldResults)) 
            $fixed['lat'] = $oldResults['lat'];
        else 
            $fixed['lat'] = 0.0;

        return $fixed;
    }
}