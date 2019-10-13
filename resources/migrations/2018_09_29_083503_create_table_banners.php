<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBanners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'banners',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('link');
                $table->string('image')
                    ->nullable();
                $table->boolean('status')
                    ->default(0);
                $table->timestamp('start_date')
                    ->nullable();
                $table->timestamp('end_date')
                    ->nullable();
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
        Schema::drop('banners');
    }
}
