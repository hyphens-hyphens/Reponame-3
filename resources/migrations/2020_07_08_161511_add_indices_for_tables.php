<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndicesForTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table){
            $table->index(['created_at', 'status_code']);
            $table->index(['pay_method', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('posts', function (Blueprint $table){
            $table->index(['slug']);
            $table->index(['group_slug', 'group_sub']);
        });

        Schema::table('users', function (Blueprint $table){
            $table->index(['name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
