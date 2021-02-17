<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdvertCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('advert_categories')->insert([
            'title'=>'Butas'
        ]);
        DB::table('advert_categories')->insert([
            'title'=>'Namas'
        ]);
    }
}
