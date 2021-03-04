<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateREWebPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('r_e_web_pages', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->unsignedInteger('category'); //butas, 
            $table->unsignedInteger('type'); //parduoda, perka, nuomuoja 
            $table->foreignId('website'); //svetaine, 
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
        Schema::dropIfExists('r_e_web_pages');
    }
}
