<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the subscription table
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chargebee_subscriptions', function($table) {
            $table->increments('id');
            $table->string('subscription_id');
            $table->string('plan_id');
            $table->string('status')->nullable();
            $table->integer('owner_id')->index()->unsigned();
            $table->integer('quantity')->default(1);
            $table->integer('last_four')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
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
        Schema::drop('chargebee_subscriptions');
    }
};
