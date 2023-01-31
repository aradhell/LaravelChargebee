<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateAddonsTable extends Migration
{
    /**
     * Create the subscription table
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chargebee_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable();
            $table->string('customer_id');
            $table->integer('amount');
            $table->string('base_currency_code')->nullable();
            $table->string('currency_code')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->string('gateway')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->boolean('deleted')->nullable();
            $table->float('exchange_rate')->nullable();
            $table->string('subscription_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('chargebee_transactions');
    }
}
