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
        Schema::create('chargebee_addons', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('chargebee_subscription_id')->index()->unsigned();
            $table->string('name');
            $table->string('addon_id');
            $table->integer('quantity')->default(0);
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
        Schema::drop('addons');
    }
};
