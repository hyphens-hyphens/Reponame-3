<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'payments',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('username');
                $table->string('card_pin');
                $table->string('card_serial');
                $table->string('card_type');
                $table->string('transaction_id')->nullable();
                $table->string('utm_source')->nullable();
                $table->string('utm_medium')->nullable();
                $table->string('utm_campaign')->nullable();
                $table->string('pay_method')->nullable();
                $table->string('pay_from')->nullable();
                $table->integer('user_id')->nullable();
                $table->integer('server_id')->nullable();
                $table->integer('payment_type')->nullable();
                $table->integer('card_amount')->nullable();
                $table->integer('gamecoin')->nullable();
                $table->integer('gamecoin_promotion')->default(0);
                $table->boolean('status')->default(0);
                $table->boolean('finished')->default(0);
                $table->boolean('gold_added')->default(0);
                $table->boolean('gateway_status')->default(0);
                $table->string('gateway_response')->nullable();
                $table->string('gateway_amount')->nullable();
                $table->string('ip')->nullable();
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
        Schema::dropIfExists('payments');
    }
}
