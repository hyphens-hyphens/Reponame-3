<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use T2G\Common\Models\GiftCode;

class AlterGiftCodeAddOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gift_codes', function (Blueprint $table){
            $table->integer('order')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gift_codes', function (Blueprint $table){
            $table->dropColumn('order');
        });
    }
}
