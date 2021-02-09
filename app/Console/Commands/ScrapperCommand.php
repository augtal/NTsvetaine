<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;

class ScrapperCommand extends Command
{
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
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $client = new Client();
        $url = 'https://www.symfony.com/blog/';

        $crawler = $client->request('GET', $url);

        $crawler->filter('h2 > a')->each(function ($node) {
            print $node->text()."\n";
        });


        
        return 0;
    }
}
