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
            $table->float('area', 6,2);
            $table->string('rooms',20);
            $table->string('floor',15);
            $table->unsignedInteger('category'); //butas, 
            $table->unsignedInteger('type'); //parduoda, perka, nuomuoja 
            $table->string('buildingType',15);
            $table->string('heating',30);
            $table->string('adress');
            $table->foreignId('website'); //svetaine, 
            $table->text('description');
            $table->string('thumbnail'); //gonna be saved in local file storage
            $table->double('long', 9, 6);
            $table->double('lat', 8, 6);
            $table->timestamps();
            
            $table->foreign('category')->references('id')->on('advert_categories');
            $table->foreign('type')->references('id')->on('advert_types');
            $table->foreign('website')->references('id')->on('r_e_websites');
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
