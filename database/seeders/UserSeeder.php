<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //email:admin@admin.com
        //password:admin123
        DB::table('users')->insert([
            'id' => '1',
            'user_name' => 'AdminName',
            'email' => 'admin@admin.com',
            'email_verified_at' => null,
            'password' => '$2y$10$C.xrIT3vRSNv6XV2QhIUjOFo/l.PwG5nf5/qOayyo.w1BYEmilGm6',
            'role' => 73,
            'remember_token' => null,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        //email:user@user.com
        //password:user12345
        DB::table('users')->insert([
            'id' => '2',
            'user_name' => 'UserName',
            'email' => 'user@user.com',
            'email_verified_at' => null,
            'password' => '$2y$10$kcZ28AKK29CxJu3ShqHVzOcHkzrcb7IeygoiSzppe0ZI.BHPRCnR.',
            'role' => 1,
            'remember_token' => null,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}
