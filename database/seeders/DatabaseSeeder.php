<?php

namespace Database\Seeders;

use App\Models\REWebPages;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            REWebsitesSeeder::class,
            AdvertTypesSeeder::class,
            AdvertCategoriesSeeder::class,
            REWebPagesSeeder::class
        ]);
    }
}
