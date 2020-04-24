<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use T2G\Common\Models\GiftCode;

class RenewGiftCodesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('gift_codes');
        Schema::create(
            'gift_codes',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('prefix', 10);
                $table->enum('type', [GiftCode::TYPE_PER_ACCOUNT, GiftCode::TYPE_PER_SERVER, GiftCode::TYPE_PER_CHARACTER]);
                $table->boolean('status')->default(false);
                $table->timestamp('expired_date')->nullable();
                $table->timestamps();
            }
        );
        Schema::dropIfExists('gift_code_items');
        Schema::create(
            'gift_code_items',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('gift_code_id', false, true);
                $table->string('code');
                $table->integer('user_id', false, true)->nullable();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gift_codes');
        Schema::dropIfExists('gift_code_items');
    }
}
