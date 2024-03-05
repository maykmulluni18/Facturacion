<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantSuppliesLogsTable extends Migration
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
        Schema::create('supplies_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('amount');
            $table->integer("stock_start");
            $table->integer("stock_end");
            $table->float("unit_price");
            $table->float("total");
            $table->unsignedInteger("supplies_id");
            $table->integer("purchases_id");
            $table->timestamps();
            $table->foreign('supplies_id')->references('id')->on('supplies')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplies_logs');

    }
}
