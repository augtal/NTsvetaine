<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationAdvertisementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id');
            $table->foreignId('advertisement_id');

            $table->foreign('notification_id')->references('id')->on('notifications');
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
        Schema::dropIfExists('notification_advertisements');
    }
}
