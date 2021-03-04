<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;

#enumeratoriu lenteles
use App\Models\AdvertCategories;
use App\Models\AdvertTypes;

#lenteles i kurias bus saugojama info
use App\Models\Advertisement;
use App\Models\AdvertisementDetails;
use App\Models\AdvertisementPrices;

#nekilnojamo turto svetainiu sarasas
use App\Models\REWebPages;
use App\Models\REWebsites;

class WebScrapperController extends Controller
{
    private $results = Array();
    private $i = 0;

    public function index(){
        $REWebsiteList = REWebPages::all()->toArray();

        foreach($REWebsiteList as $website){
            $this->scrape($website);
        }

        echo "End of index";
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

        $pages = $adAmount / $adsPerPage;
        $pages = 1;

        for ($i = 1; $i <= $pages; $i++) {
            $url = $website['url'];
            $crawler = $client->request('GET', $url);

            $adsInfo = Array();
            $crawler->filter('div.item')->each(function ($node) use (&$adsInfo){
                $info = Array();

                $info['url'] = $node->filter('.item-section.fr > h2 > a')->link()->getUri();

                $info['title'] = $node->filter('.item-section.fr > h2 > a')->text();
                $info['area'] = (double)substr($node->filter('.item-section.fr > div.param-list > div > span')->text(), 0, -3);

                $price = $node->filter('.item-section.fr > div.price > p.fl > strong')->text();
                $price = substr($price, 0, -4); # to remove € with a space before 
                $price = str_replace(' ', '', $price);
                $info['price'] = (int)$price;
                
                $info['imgUrl'] = $node->filter('div > div.thumb.fl > a > img')->attr('src');

                array_push($adsInfo, $info);
            });

            $i = 1;
            foreach($adsInfo as $info){
                if( strpos($info['url'], 'domoplius') !== FALSE){
                    #check if add exists
                    $adID = Advertisement::where('title', $info['title'])->where('area', $info['area'])->first();
                    if($adID != null){
                        #update price
                        $adID->touch();
                        AdvertisementDetails::where('advertisementID', $adID->id)->first()->touch();
                        $this->updateAdvertisementPrices($info['price'], $adID->id);
                    }
                    else{
                        #create new
                        $result = $this->scrapeDomoSingle($client, $info['url']);
                        $detailedInfo = $this->fixResultsDomo($result);
                        $id = $this->insertToAdvertisement($website, $info, $detailedInfo);
                        $this->insertToAdvertisementDetails($detailedInfo, $id);
                        $this->insertToAdvertisementPrices($info, $id);
                    }
                    
                }
                $i += 1;
                echo "finished " . $i . '\n';
            }
        }
    }

    private function insertToAdvertisement($website, $adsInfo, $detailedInfo){
        $advertisement = new Advertisement();

        $advertisement->title = $adsInfo['title'];
        $advertisement->category = $website['category'];
        $advertisement->type = $website['type'];
        $advertisement->area = $adsInfo['area'];
        $advertisement->website = $website['website']; 
        $advertisement->thumbnail = $adsInfo['imgUrl'];
        $advertisement->url = $adsInfo['url'];
        $advertisement->long = $detailedInfo['long'];
        $advertisement->lat = $detailedInfo['lat'];
        $advertisement->save();

        $id = Advertisement::where('title', $adsInfo['title'])->first()->id;

        return $id;
    }

    private function insertToAdvertisementDetails($detailedInfo, $id){
        $details = new AdvertisementDetails();

        $details->advertisementID = $id;
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

        $prices->advertisementID = $id;
        $prices->price = $adsInfo['price'];
        $prices->save();
    }

    private function updateAdvertisementPrices($adsPrice, $id){
        #updates old price updated_at field, by imitating a change
        $oldPrice = AdvertisementPrices::where('advertisementID', $id)->orderBy('updated_at', 'desc')->first()->touch();

        #sets new price
        $newPrice = new AdvertisementPrices();
        $newPrice->advertisementID = $id;
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
        #$description = $crawler->filter('div.medium.info-block > div:nth-child(12)')->text();
        $description = "Nera";
        $results['description'] = $description;

        #long/lat
        $mapLink = $crawler->filter('a#mini-map-block')->link()->getUri();
        $crawler = $client->request('GET', $mapLink);

        $longAndLat = $crawler->filter('#container > section > div.small-wrapper > div.content-wrapper > main > script:nth-child(5)')->text();
        if (preg_match_all("/\d{1,3}\.\d{6}/", $longAndLat, $values)){
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