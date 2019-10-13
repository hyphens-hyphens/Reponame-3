<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPaymentsAddCreatorIdAndNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('card_pin')->nullable()->change();
            $table->string('card_serial')->nullable()->change();
            $table->string('card_type')->nullable()->change();
            $table->string('note')->nullable();
            $table->integer('creator_id')->nullable();
            $table->integer('amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('card_pin')->change();
            $table->string('card_serial')->change();
            $table->string('card_type')->change();
            $table->dropColumn('note');
            $table->dropColumn('creator_id');
            $table->dropColumn('amount');
        });
    }
}
