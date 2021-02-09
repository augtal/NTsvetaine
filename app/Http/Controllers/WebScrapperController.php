<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;

class WebScrapperController extends Controller
{
    private $results = Array();
    private $i = 0;

    public function index(){
        $client = new Client();
        $url = 'https://domoplius.lt/';

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
