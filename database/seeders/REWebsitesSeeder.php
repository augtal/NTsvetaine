<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class REWebsitesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('r_e_websites')->insert([
            'title'=>'Domoplius',
            'URL'=>'https://domoplius.lt/',
            'logo'=>'http://static.domoplius.lt/domoplius/img/svg/domoplius/domoplius-logo.svg',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}
