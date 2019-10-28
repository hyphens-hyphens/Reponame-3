<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableClientTracking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_tracking', function (Blueprint $table) {
            $table->increments('id');
            $table->string('version')->nullable();
            $table->string('host')->nullable();
            $table->string('ethernet_mac')->nullable();
            $table->string('wifi_mac')->nullable();
            $table->string('local_ip')->nullable();
            $table->string('external_ip')->nullable();
            $table->string('signature');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_tracking');
    }
}
