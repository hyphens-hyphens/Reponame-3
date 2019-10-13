<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('utm_source')->nullable();
            $table->text('utm_medium')->nullable();
            $table->text('utm_campaign')->nullable();
            $table->text('registered_ip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('utm_source');
            $table->dropColumn('utm_medium');
            $table->dropColumn('utm_campaign');
            $table->dropColumn('registered_ip');
        });
    }
}
