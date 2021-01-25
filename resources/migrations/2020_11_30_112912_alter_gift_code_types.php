<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use T2G\Common\Models\GiftCode;

class AlterGiftCodeTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gift_codes', function (Blueprint $table){
            $table->enum('type', [
                GiftCode::TYPE_PER_ACCOUNT,
                GiftCode::TYPE_PER_SERVER,
                GiftCode::TYPE_PER_CHARACTER,
                GiftCode::TYPE_PER_MONTH,
                GiftCode::TYPE_FAN_CUNG,
            ])->change();
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
            $table->enum('type', [
                GiftCode::TYPE_PER_ACCOUNT,
                GiftCode::TYPE_PER_SERVER,
                GiftCode::TYPE_PER_CHARACTER,
            ])->change();
        });
    }
}
