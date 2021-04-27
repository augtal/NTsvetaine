<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisementLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisement_locations', function (Blueprint $table) {
            $table->foreignId('advertisement_id')->unique();
            $table->double('lat', 8, 6);
            $table->double('lng', 9, 6);

            $table->foreign('advertisement_id')->references('id')->on('advertisements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advertisement_locations');
    }
}
