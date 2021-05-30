<?php

namespace App\Console\Commands;

use App\Traits\FindNotificationsTrait;
use App\Traits\CreateUserMessageTrait;

use Illuminate\Console\Command;
use Goutte\Client;

#lenteles i kurias bus saugojama info
use App\Models\Advertisement;
use App\Models\AdvertisementLocation;
use App\Models\AdvertisementDetails;
use App\Models\AdvertisementPrices;

#nekilnojamo turto svetainiu sarasas
use App\Models\REWebPages;
use App\Models\REWebsites;
use App\Traits\ArchiveOldAdvertisemetsTrait;

class ScrapperCommand extends Command
{
    use FindNotificationsTrait;
    use CreateUserMessageTrait;
    use ArchiveOldAdvertisemetsTrait;

    private $DomoAdsPerPage = 30;
    private $NTportalasAdsPerPage = 25;
    private $CapitalAdsPerPage = 20;

    private $limiter = 1;

    private $report;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrapper:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts web scrapper';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->report = [   
            'success' => 0,
            'failed' => 0,
            'items' => [],
            'errors' => []
        ];
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        print "======= Web scrapper started =======\n";
        $REWebsiteList = REWebsites::all();

        foreach($REWebsiteList as $website){
            $this->scrape($website);
        }

        $this->archiveAdvertisements();
        $this->sendNotifications();

        print "\n========= End of scrapping =========\n";


        $this->showReport();
        return 0;
    }

    /**
     * Creates webscrapping report in console
     *
     * @return void
     */
    private function showReport(){
        print "============== Report ==============\n";


        print "============== Items ==============\n";
        foreach($this->report['items'] as $item){
            print $item;
            print "\n";
        }

        print "============== Errors ==============\n";
        foreach($this->report['errors'] as $errors){
            foreach($errors as $error){
                print $error;
                print "\n";
            }
        }

        print "============== Totals ==============\n";
        print "Successful scrapes: " . $this->report['success'];
        print "\nFailed scrapes: " . $this->report['failed'];

        print "\n=========== End of Report ==========\n";
    }

    /**
     * Gets all website webpages that needs scrapping
     *
     * @param object $website Real estate website information from database
     * @return void
     */
    private function scrape($website){
        $websitePages = REWebPages::where('r_e_websites_id', $website->id)->get();
        
        if($website->title == 'Domoplius'){
            foreach($websitePages as $REWebPage){
                $this->scrapeREWebsite($REWebPage, $website);
            }
        }
        elseif($website->title == 'Capital'){
            foreach($websitePages as $REWebPage){
                $this->scrapeREWebsite($REWebPage, $website);
            }
        }
    }

    /**
     * Main method for scraping real estate websites
     *
     * @param object $REWebPage Real estate website advertisement page information from database
     * @param object $website Real estate website information from database
     * @return void
     */
    private function scrapeREWebsite($REWebPage, $website){
        $client = new Client();
        $crawler = $client->request('GET', $REWebPage['url']);
        $title = strtolower($website->title);

        try {
            switch ($title){
                case 'domoplius':
                    $adAmount = (int)substr($crawler->filter('div.cntnt-box-fixed > div.listing-title > span')->text(), 1, -1);
    
                    if($adAmount % $this->DomoAdsPerPage > 0) 
                        $pages = (int)($adAmount / $this->DomoAdsPerPage + 1);
                    else 
                        $pages = $adAmount / $this->DomoAdsPerPage;
                    break;
                case 'capital':
                    $adAmountText = explode("(", $crawler->filter('div.realty-items-container.col-md-9 > div.realty-items-top > div.left-side > div.serch-results > strong')->text());
                    $adAmountText = $adAmountText[count($adAmountText)-1];
                    $adAmount = (int)substr($adAmountText, 0, strlen($adAmountText)-1);
    
                    if($adAmount % $this->CapitalAdsPerPage > 0) 
                        $pages = (int)($adAmount / $this->CapitalAdsPerPage + 1);
                    else 
                        $pages = $adAmount / $this->CapitalAdsPerPage;
                    break;
            }
        } 
        catch (\InvalidArgumentException $e) {
            $trace = $e->getTrace();
            $error = [
            "Error in method: " . $trace[1]['function'] . " on line: " . $trace[0]['line'],
            "Error URL: ". $REWebPage['url'],
            "Error message: " . $e->getMessage(),
            ];
            $this->report['failed']++;
            $this->report['errors'][] = $error;
        }

        #------------------------------------------------------------------------------------------------------------------------------------------------
        #remove limiter
        if($this->limiter != 0) $pages = $this->limiter;
        #------------------------------------------------------------------------------------------------------------------------------------------------
        
        $number=0;
        for ($i = 1; $i <= $pages; $i++) {
            //$REWebPage['url'] turi baigtis ?page=X
            $url = substr($REWebPage['url'], 0, -1) . $i;
            $crawler = $client->request('GET', $url);

            $adsInfo = Array();
            try{
                switch ($title){
                    case 'domoplius':
                        $adsInfo = $this->getDomoPageAdsInfo($crawler);
                        break;
                    case 'capital':
                        $adsInfo = $this->getCapitalPageAdsInfo($crawler);
                        break;
                }
            } 
            catch (\InvalidArgumentException $e) {
                $trace = $e->getTrace();
                $error = [
                "Error in method: " . $trace[1]['function'] . " on line: " . $trace[0]['line'],
                "Error URL: ". $REWebPage['url'],
                "Error message: " . $e->getMessage(),
                ];
                $this->report['failed']++;
                $this->report['errors'][] = $error;
            }

            foreach($adsInfo as $info){
                $action = "";
                if( strpos($info['url'], $title) == TRUE){
                    #patikrinti ar skelbimas is sitos svetaines jau yra
                    $adID = Advertisement::where('title', $info['title'])->where('area', $info['area'])->where('r_e_websites_id', $website->id)->where('url', $info['url'])->first();
                    if($adID != null){
                        #jeigu skelbimas nera archyvuotas
                        if($adID->archived == 0){
                            #atnaujinti kaina
                            $adID->touch();
                            AdvertisementDetails::where('advertisement_id', $adID->id)->first()->touch();
                            $this->updateAdvertisementPrices($info['price'], $adID->id);
                            $action = "U |";
                        }
                    }
                    else{
                        #sukurti nauja
                        $result = Array();
                        $detailedInfo = Array();

                        try{
                            switch ($title){
                                case 'domoplius':
                                    $result = $this->scrapeDomoSingle($client, $info['url']);
                                    $detailedInfo = $this->fixResultsDomo($result);
                                    break;
                                case 'capital':
                                    $result = $this->scrapeCapitalSingle($client, $info['url']);
                                    $detailedInfo = $this->fixResultsCapital($result);
                                    break;
                            }
                        } 
                        catch (\InvalidArgumentException $e) {
                            $trace = $e->getTrace();
                            $error = [
                            "Error in method: " . $trace[1]['function'] . " on line: " . $trace[0]['line'],
                            "Error URL: ". $info['url'],
                            "Error message: " . $e->getMessage(),
                            ];
                            $this->report['failed']++;
                            $this->report['errors'][] = $error;
                            continue;
                        }

                        $advertisement = $this->insertToAdvertisement($REWebPage, $info, $detailedInfo);
                        $this->downloadAdvertisementThumbnail($advertisement);
                        $this->insertToAdvertisementLocation($detailedInfo, $advertisement->id);
                        $this->insertToAdvertisementDetails($detailedInfo, $advertisement->id);
                        $this->insertToAdvertisementPrices($info, $advertisement->id);
                        $action = "C |";
                    }
                }
                $number += 1;
                $this->report['success']++;
                $this->report['items'][] = $action . " finished " . $number;
            }
        }
        return;
    }

    /**
     * Gets advertisement list primary information, without going into seperate advertisements
     *
     * @param object $crawler Webscrapper instance
     * @return array $adsInfo Scraped advertisements information
     */
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

    /**
     * Exracts information from a single advertisement from domoplius.lt website
     *
     * @param object $client Webscrapper instance
     * @param string $link Advertisements URL
     * @return array $results Returns scraped data from advertisement
     */
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
        $descriptionMarker = "div.col-right > div.medium.info-block > div:nth-child(" . $amount - 3 . ")"; # - 3, nes komentarai yra 3 nuo galo \n<div><div>
        if($crawler->filter($descriptionMarker)->count()){
            $description = $crawler->filter($descriptionMarker)->html();#issaugomas su \n
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

    /**
     * Fixes scraped data from advertisement so that it's ready to be inserted to database
     *
     * @param array $oldResults 
     * @return array $fixed Returns fixed array results
     */
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

    /**
     * Gets advertisement list primary information, without going into seperate advertisements
     *
     * @param object $crawler Webscrapper instance
     * @return array $adsInfo Scraped advertisements information
     */
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

    /**
     * Exracts information from a single advertisement from capital.lt website
     *
     * @param object $client Webscrapper instance
     * @param string $link Advertisements URL
     * @return array $results Returns scraped data from advertisement
     */
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
            $description = explode('<a', $crawler->filter('div.realty-information-container.col-md-6 > div.realty-description')->html())[0];#issaugomas su \n
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

    /**
     * Fixes scraped data from advertisement so that it's ready to be inserted to database
     *
     * @param array $oldResults 
     * @return array $fixed Returns fixed array results
     */
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

    /**
     * Updates advertisement price with a new scraped value
     *
     * @param double $adsPrice Scraped advertisement price
     * @param integer $id Advertisement ID
     * @return void
     */
    private function updateAdvertisementPrices($adsPrice, $id){
        #atnaujinti sena kainu lauka
        $oldPrice = AdvertisementPrices::where('advertisement_id', $id)->orderBy('id', 'desc')->first();
        $oldPrice->touch();

        $changeAmount = round((($adsPrice * 100) / $oldPrice->price) - 100, 1);

        #iterpti nauja kainos irasa
        $newPrice = new AdvertisementPrices();
        $newPrice->advertisement_id = $id;
        $newPrice->price = $adsPrice;
        $newPrice->priceChange = $changeAmount;
        $newPrice->save();
    }

    /**
     * Inserts advertisement to database
     *
     * @param object $REWebPage Real estate website advertisement page information from database
     * @param array $adsInfo Primary scrape data
     * @param array $detailedInfo Detailed single advertisement scrape data
     * @return object $advertisement Returns inserted advertisement object
     */
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

    /**
     * Inserts advertisement location to database
     *
     * @param array $detailedInfo Detailed single advertisement scrape data
     * @param integer $id Advertisement ID
     * @return void
     */
    private function insertToAdvertisementLocation($detailedInfo, $id){
        $location = new AdvertisementLocation();

        $location->advertisement_id = $id;
        $location->lat = $detailedInfo['lat'];
        $location->lng = $detailedInfo['lng'];
        $location->save();
    }

    /**
     * Inserts advertisement details to database
     *
     * @param array $detailedInfo Detailed single advertisement scrape data
     * @param integer $id Advertisement ID
     * @return void
     */
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

    /**
     * Inserts advertisement price to database
     *
     * @param array $adsInfo Primary scrape data
     * @param integer $id Advertisement ID
     * @return void
     */
    private function insertToAdvertisementPrices($adsInfo, $id){
        $prices = new AdvertisementPrices();

        $prices->advertisement_id = $id;
        $prices->price = $adsInfo['price'];
        $prices->priceChange = 0;
        $prices->save();
    }

    /**
     * Downloads advertisement thumbnail and saves it to public/images/AdvertisementsThumbnails/ directory
     *
     * @param object $advertisement Advertisement information
     * @return void
     */
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

            file_put_contents("public/images/AdvertisementsThumbnails/" . $fileName, $data);
        }
    }

    /**
     * Downloads website logo and saves it to public/images/RealEstateWebsiteLogos directory
     *
     * @param string $url Website logo url
     * @param integer $id Website ID that it's saved in database
     * @return void
     */
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

            file_put_contents("public/images/RealEstateWebsiteLogos/" . $fileName, $data);
        }
    }

}
