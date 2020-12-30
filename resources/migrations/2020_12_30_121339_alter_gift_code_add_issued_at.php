<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGiftCodeAddIssuedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gift_code_items', function (Blueprint $table){
            // value is user_id that was issued the code item
            $table->timestamp('issued_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gift_code_items', function (Blueprint $table){
            $table->dropColumn('issued_at');
        });
    }
}
