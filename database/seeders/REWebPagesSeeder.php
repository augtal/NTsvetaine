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
            'url'=>'https://domoplius.lt/skelbimai/butai?action_type=1&page_nr=1',
            'category'=>1,
            'type'=>1,
            'r_e_websites_id'=>1,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        DB::table('r_e_web_pages')->insert([
            'url'=>'https://domoplius.lt/skelbimai/butai?action_type=3&page_nr=1',
            'category'=>1,
            'type'=>2,
            'r_e_websites_id'=>1,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        DB::table('r_e_web_pages')->insert([
            'url'=>'https://domoplius.lt/skelbimai/namai-kotedzai-sodai?action_type=1&page_nr=1',
            'category'=>2,
            'type'=>1,
            'r_e_websites_id'=>1,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        DB::table('r_e_web_pages')->insert([
            'url'=>'https://domoplius.lt/skelbimai/namai-kotedzai-sodai?action_type=3&page_nr=1',
            'category'=>2,
            'type'=>2,
            'r_e_websites_id'=>1,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        DB::table('r_e_web_pages')->insert([
            'url'=>'https://www.capital.lt/lt/nekilnojamas-turtas/butai-pardavimui?page=1',
            'category'=>1,
            'type'=>1,
            'r_e_websites_id'=>2,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        DB::table('r_e_web_pages')->insert([
            'url'=>'https://www.capital.lt/lt/nekilnojamas-turtas/butai-nuomai?page=1',
            'category'=>1,
            'type'=>2,
            'r_e_websites_id'=>2,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}
