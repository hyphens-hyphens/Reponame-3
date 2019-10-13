<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGiftCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'gift_codes',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('code');
                $table->timestamp('expired_date')->nullable();
                $table->integer('user_id')->nullable();
                $table->boolean('is_used')->default(0);
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
    }
}
