<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisementDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisement_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id'); //svetaine, 
            $table->string('rooms',20);
            $table->string('floor',25);
            $table->string('buildingType',15);
            $table->string('heating',30);
            $table->string('year', 12);
            $table->text('description');

            $table->timestamps();
            
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
        Schema::dropIfExists('advertisement_details');
    }
}
