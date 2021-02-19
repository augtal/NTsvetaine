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

class WebScrapperController extends Controller
{
    private $results = Array();
    private $i = 0;

    public function index(){
        $url = 'https://domoplius.lt/';

        $categories = AdvertCategories::all()->toArray();
        $types = AdvertTypes::all()->toArray();

        $AdScrappingDetails = Array([
            ""
        ]);
        $DetailedResults = Array([
            "adress" => "Vilnius, Šnipiškės, Giedraičių g., ",
            "Kaina" => "224 000 €",
            "1 kv. m kaina:" => "3 200 €",
            "Siūlyti savo kainą" => "Informuoti, kai kaina kris",
            "Kambarių skaičius:" => "3",
            "Buto plotas (kv. m):" => "70.00 kv. m.",
            "Statybos metai:" => "2017",
            "Aukštas:" => "4, 4 aukštų pastate",
            "Būklė:" => "Įrengtas",
            "Šildymas:" => "Centrinis kolektorinis",
            "Namo tipas:" => "Mūrinis",
            "description" => "ŠNIPIŠKĖS - VISIEMS LABAI GERAI ŽINOMAS GULBE VIRSTANTIS BJAURUSIS ANČIUKAS. SPARČIAI DYGSTANTYS PASTATAI, TVARKOMOS GATVĖS, NAUJAI SUFORMUOTI DVIRAČIŲ TAKAI",
            "long" => "54.706326",
            "lat" => "25.280096",
        ]);


        #return $this->simpleScrape($url);
        #$this->complexScrape2();
        #return $this->complexScrape($url);
        echo "End";
        die;
    }

    private function complexScrape(){
        /*$client = new Client();
        $url = 'https://domoplius.lt/skelbimai/butai?action_type=1';

        $crawler = $client->request('GET', $url);

        $links = $crawler->filter('.item.lt')->each(function ($node){
            return $node->filter('.item-section.fr > h2 > a')->link()->getUri();
        });

        foreach($links as $link){
            if( strpos($link, 'domoplius') !== FALSE){
                
            }
        }*/

        #WORKING ^^^

        #=====================================================================================================
        $results = Array();

        #==========================================================
        $client = new Client();
        $url = 'https://domoplius.lt/skelbimas/parduodamas-3-kambariu-butas-vilniuje-snipiskese-giedraiciu-g-7218598.html';

        $crawler = $client->request('GET', $url);
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
        $description = $crawler->filter('div.medium.info-block > div:nth-child(16)')->text();
        $results['description'] = $description;

        #long/lat
        $mapLink = $crawler->filter('a#mini-map-block')->link()->getUri();
        $crawler = $client->request('GET', $mapLink);

        $longAndLat = $crawler->filter('#container > section > div.small-wrapper > div.content-wrapper > main > script:nth-child(5)')->text();
        if (preg_match_all("/\d{1,3}\.\d{6}/", $longAndLat, $values)){
            print_r("Found coordinates");
            $results['long'] = $values[0][0];
            $results['lat'] = $values[0][1];
        }
        else{
            print_r("Error while getting coordinates");
        }
        

        dd($results);













        /*
        //limiter
        $links = array_slice($links, 0, 5);

        $crawler = $client->request('GET', $links[0]);
        */

        /*
        $crawler->filter('.items-slide > ul > li')->each(function ($node) {
            $this->results[$this->i] = Array(
                'img' => $node->filter('.thumbnail > img')->image()->getUri(),
                'url' => $node->filter('a')->link()->getUri(),
                'price' => $node->filter('.content > .price')->text(),
                'description' => $node->filter('.content > .description')->text()
                'title' => $node->filter('.content > .title')->text()
                
            );
            $this->i += 1;
        });
        $this->i = 0;
        */

        /* Gauna kategorijas is pradinio domoplius.lt puslapio
        $categories = $crawler->filter('.search-form-field')->first()->filter('.dropdown-options-items > label')->each(function ($node){
            return $node->text();
        });
        dd($categories);

        /html/body/div[2]/div/section/div[1]/div[1]/main/div[4]/div[2]/div[1]/table[1]/tbody/tr/td/strong
        //*[@id="container"]/section/div[1]/div[1]/main/div[4]/div[2]/div[1]/table[1]/tbody/tr/td/strong
        #container > section > div.small-wrapper > div.content-wrapper > main > div:nth-child(8) > div.col-right > div.medium.info-block > table.view-group.price-format > tbody > tr > td > strong
        
        */

    }

    private function complexScrape2(){
        $client = new Client();
        $url = 'https://www.symfony.com/blog/';
        
        $crawler = $client->request('GET', $url);

        $info = $crawler->filter('h2 > a')->each(function ($node) {
            return $node->link()->getUri();
        });

        foreach($info as $link){
            $crawler = $client->request('GET', $link);

            $info2 = $crawler->filter('.post__content > p')->each(function ($node){
                return $node->text();
            });

            echo print_r($info2) . "<br>";
            echo print_r("--------------------") . "<br>";
        }
    }

    private function simpleScrape($siteUrl){ //depricated
        $client = new Client();
        $url = $siteUrl;

        $crawler = $client->request('GET', $url);

        $crawler->filter('.items-slide > ul > li')->each(function ($node) {
            $this->results[$this->i] = Array(
                'img' => $node->filter('.thumbnail > img')->image()->getUri(),
                'url' => $node->filter('a')->link()->getUri(),
                'price' => $node->filter('.content > .price')->text(),
                'description' => $node->filter('.content > .description')->text()
            );
            $this->i += 1;
        });
        $this->i = 0;

        return view('scrape')->with('data', $this->results);
    }

    
}
