<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdvertTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('advert_types')->insert([
            'title'=>'Parduodama'
        ]);
        DB::table('advert_types')->insert([
            'title'=>'Nuomuojama'
        ]);
    }
}
