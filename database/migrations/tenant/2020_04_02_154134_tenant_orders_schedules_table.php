<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantOrdersSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//
        /*
         ***************************************************************************************************************
         ***************************************************************************************************************
         ***************************************************************************************************************
         */
        Schema::create('orders_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("contract_id");
            $table->timestamps();
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
        });


    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders_schedules');

    }
}
