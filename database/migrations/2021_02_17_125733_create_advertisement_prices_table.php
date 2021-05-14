<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisementPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisement_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id'); //svetaine, 
            $table->double('price', 10,2);
            $table->double('priceChange', 4,1);
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
        Schema::dropIfExists('advertisement_prices');
    }
}
