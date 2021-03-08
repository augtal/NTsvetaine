<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedInteger('category'); //butas, 
            $table->unsignedInteger('type'); //parduoda, perka, nuomuoja 
            $table->float('area', 10,2);
            $table->foreignId('r_e_websites_id'); //svetaine, 
            $table->string('thumbnail'); //gonna be saved in local file storage
            $table->string('url');
            $table->double('long', 9, 6);
            $table->double('lat', 8, 6);
            $table->timestamps();
            
            $table->foreign('category')->references('id')->on('advert_categories');
            $table->foreign('type')->references('id')->on('advert_types');
            $table->foreign('r_e_websites_id')->references('id')->on('r_e_websites');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertisements');
    }
}
