<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class REWebPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('r_e_web_pages')->insert([
            'title'=>'Domoplius',
            'url'=>'https://domoplius.lt/',
            'logo'=>'http://static.domoplius.lt/domoplius/img/svg/domoplius/domoplius-logo.svg',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        /*$table->id();
            $table->string('url');
            $table->unsignedInteger('category'); //butas, 
            $table->unsignedInteger('type'); //parduoda, perka, nuomuoja 
            $table->foreignId('website'); //svetaine, 
            $table->timestamps();
        */
    }
}
